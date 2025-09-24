<?php
    $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc";
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    }
    $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc/".SIS_EMPRESA_RUC;
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    }
    $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo";
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    } 
    $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id";
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    }