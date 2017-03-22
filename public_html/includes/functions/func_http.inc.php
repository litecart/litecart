<?php

  function http_fetch($url, $post_data=null, $headers=array(), $asynchronous=false, $follow_redirects=true, $return='body') {

    trigger_error('http_fetch() has been deprecated, use instead http_client::call()', E_USER_DEPRECATED);

    $client = new http_client(array('asynchronous' => $asynchronous, 'follow_redirects' => $follow_redirects));
    $result = $client->call($url, $post_data, $headers);

    switch ($return) {
      case 'both':
        return $client->last_response['header'] . $client->last_response['body'];
      case 'header':
        return $client->last_response['header'];
      case 'body':
      default:
        return $client->last_response['body'];
    }

    return $result;
  }
