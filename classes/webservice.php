<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_zoomsdk;

defined('MOODLE_INTERNAL') || die();

/**
 * Webservice class per chiamate API Zoom - versione indipendente per SDK
 */
class webservice {
    
    /**
     * API base URL
     */
    const API_URL = 'https://api.zoom.us/v2/';
    
    /**
     * JWT Token
     */
    private $token;
    
    /**
     * Constructor - genera JWT token
     */
    public function __construct() {
        $this->token = $this->generate_jwt_token();
    }
    
    /**
     * Genera JWT token usando le credenziali del plugin zoom
     */
    private function generate_jwt_token() {
        $config = get_config('zoom');
        
        if (empty($config->apikey) || empty($config->apisecret)) {
            throw new \moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 
                'Credenziali Zoom API (apikey/apisecret) non configurate in mod_zoom');
        }
        
        $apikey = $config->apikey;
        $apisecret = $config->apisecret;
        
        // Header JWT
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);
        
        // Payload JWT
        $payload = json_encode([
            'iss' => $apikey,
            'exp' => time() + 3600 // Token valido per 1 ora
        ]);
        
        // Base64 encode
        $base64_header = $this->base64url_encode($header);
        $base64_payload = $this->base64url_encode($payload);
        
        // Signature
        $signature = hash_hmac('sha256', $base64_header . "." . $base64_payload, $apisecret, true);
        $base64_signature = $this->base64url_encode($signature);
        
        // JWT token
        return $base64_header . "." . $base64_payload . "." . $base64_signature;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Esegue una chiamata API a Zoom
     * 
     * @param string $endpoint Endpoint API (es: "users/me/meetings")
     * @param array $data Dati da inviare
     * @param string $method Metodo HTTP (get, post, patch, delete)
     * @return object Response JSON decodificato
     */
    public function make_call($endpoint, $data = [], $method = 'get') {
        $url = self::API_URL . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        
        // Imposta metodo HTTP
        switch (strtolower($method)) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'get':
            default:
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlerror = curl_error($ch);
        curl_close($ch);
        
        // Log per debug
        if ($httpcode >= 400) {
            error_log('=== ZOOM SDK WEBSERVICE ERROR ===');
            error_log('Endpoint: ' . $endpoint);
            error_log('Method: ' . $method);
            error_log('HTTP Code: ' . $httpcode);
            error_log('Request: ' . json_encode($data, JSON_PRETTY_PRINT));
            error_log('Response: ' . $response);
            error_log('==================================');
        }
        
        // Gestione errori HTTP
        if ($httpcode >= 400) {
            $error = json_decode($response);
            $message = isset($error->message) ? $error->message : 'Unknown error';
            throw new \moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 
                $message . ' (HTTP ' . $httpcode . ')');
        }
        
        if ($curlerror) {
            throw new \moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 
                'cURL error: ' . $curlerror);
        }
        
        return json_decode($response);
    }
    
    /**
     * Get user info
     */
    public function get_user($email) {
        return $this->make_call('users/' . urlencode($email), [], 'get');
    }
    
    /**
     * Delete meeting
     */
    public function delete_meeting($meetingid) {
        return $this->make_call('meetings/' . $meetingid, [], 'delete');
    }
}
