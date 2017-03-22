<?php

  class url_index {

    //function routes() {}

  	function rewrite($parsed_link, $language_code) {

      $parsed_link['path'] = ''; // Remove index file for site root

      return $parsed_link;
    }
  }
