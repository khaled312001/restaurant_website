<?php

namespace Config;

class Iyzipay
{
  public static function options()
  {
    $options = new \Iyzipay\Options();
    $options->setApiKey("sandbox-nhwvNYFN8EdyUm0MXVon9u9wNt6HTKrl");
    $options->setSecretKey("sandbox-nZ69wQYaUbxqKbOoHJmc9CjQZtgcSloC");
    $options->setBaseUrl("https://sandbox-api.iyzipay.com");
    return $options;
  }
}
