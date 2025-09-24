<?php
    $location=$target_dir;
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    }
    $location=$target_dir.SIS_EMPRESA_RUC;
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    }
    $location=$target_dir.SIS_EMPRESA_RUC."/$periodo";
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    } 
    $location=$target_dir.SIS_EMPRESA_RUC."/$periodo/$desp_id";
    if (!is_dir($location) && !mkdir($location, '0755', true)) {
        exit(0);
    }