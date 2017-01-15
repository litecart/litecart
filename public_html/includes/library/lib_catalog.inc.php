<?php

  class catalog {

    public static function __callStatic($resource, $arguments) {
      trigger_error(__CLASS__.'::'.$resource .' is deprecated. Use instead reference::'.$resource, E_USER_DEPRECATED);
      return forward_static_call(array('reference', $resource), $arguments);
    }
  }

?>