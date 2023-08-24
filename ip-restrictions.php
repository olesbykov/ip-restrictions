<?php
/*
Plugin Name: IP Restriction
Plugin URI: https://o-les.ru/ip-restriction
Description: Restricts access to the WordPress admin area based on IP addresses.
Version: 1.0
Author: Oles Bykov
Author URI: https://o-les.ru
License: GPL2
*/

// Хук к действию 'admin_init'
add_action('admin_init', 'ip_restriction_check');
add_action('login_init', 'ip_restriction_check');

function ip_restriction_check() {
    $allowed_ips = array(
        '127.0.0.1', // Добавьте сюда нужные вам IP-адреса
        // Добавьте остальные IP-адреса, разрешенные для доступа к админке
        //'::1', //ipv6
        //'192.168.1.100', //ipv4
    );

    // Получаем IP-адрес пользователя
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $user_ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $user_ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $user_ip = $_SERVER['REMOTE_ADDR'];
    }

    // Проверяем, является ли IP-адрес пользователя разрешенным
    // Замените RU на код необходимой страны или удалите его вовсе, чтобы проверять только список IP
    if ( ! in_array( $user_ip, $allowed_ips ) && ! ip_is_in_country( $user_ip, 'RU' ) ) {
        wp_die( 'Доступ запрещён.'/* . $_SERVER['REMOTE_ADDR']*/ );// 
    }
}

// Функция для проверки страны IP-адреса
function ip_is_in_country( $ip, $country_code ) {
    // Возвращаем true, если IP-адрес принадлежит указанной стране, и false в противном случае
    $api_url = 'http://ip-api.com/json/' . $ip;
    $response = wp_remote_get( $api_url );

    if ( is_wp_error( $response ) ) {
        // Обработка ошибки при получении данных от API
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    if ( isset( $data->countryCode ) && $data->countryCode === $country_code ) {
        return true;
    } else {
        return false;
    }
}