<?php
// includes/api_ffbad.php

/**
 * Récupère les classements FFBadminton pour une licence donnée via le webservice SOAP.
 *
 * @param string $licence
 * @return array Associatif ['simple' => string, 'double' => string, 'mixte' => string]
 */
function getClassements(string $licence): array
{
    // Si l’extension SOAP n’est pas chargée, on renvoie des valeurs par défaut
    if (! extension_loaded('soap')) {
        return ['simple' => '-', 'double' => '-', 'mixte' => '-'];
    }

    static $client = null;

    if ($client === null) {
        // URL WSDL du service de test FFBAD. En prod, tu pourras passer à https://api.ffbad.org/ServiceFFBAD.asmx?WSDL
        $wsdl = 'https://apitest.ffbad.org/ServiceFFBAD.asmx?WSDL';

        $options = [
            'trace'        => true,
            'exceptions'   => true,
            'cache_wsdl'   => WSDL_CACHE_NONE,
            'soap_version' => SOAP_1_2,
        ];

        // Création du SoapClient
        $client = new SoapClient($wsdl, $options);
    }

    try {
        // Appel de la méthode ws_getrankingbylicence
        $response = $client->ws_getrankingbylicence([
            'Licence' => $licence,
        ]);

        // Le JSON est dans la propriété <méthode>Result
        $json = $response->ws_getrankingbylicenceResult ?? '';

        // Décodage du JSON
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Erreur JSON : ' . json_last_error_msg());
        }

        // On récupère le sous-tableau "Classement"
        $c = $data['Classement'] ?? [];

        return [
            'simple' => $c['simple'] ?? '-',
            'double' => $c['double'] ?? '-',
            'mixte'  => $c['mixte'] ?? '-',
        ];
    }
    catch (SoapFault $e) {
        // En cas d’erreur SOAP, on log et on renvoie des tirets
        error_log("FFBAD SOAP error pour licence {$licence} : " . $e->getMessage());
        return ['simple' => '-', 'double' => '-', 'mixte' => '-'];
    }
}
