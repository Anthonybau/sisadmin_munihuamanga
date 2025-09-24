<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('America/Lima');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once('PHPExcel/Classes/PHPExcel.php');

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator(SIS_APL_NAME)
							 ->setLastModifiedBy(SIS_APL_NAME)
							 ->setTitle("Office 2007 XLSX $title_hoja Document")
							 ->setSubject("Office 2007 XLSX $title_hoja Document")
							 ->setDescription("Archivo de Trabajo EXCEL obtenido desde ".SIS_APL_NAME)
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("$title_hoja result file");
    $numCampos = $rs->numfields();
    /* Titulos de las columnas */
        $y=64;
        $z=64;
        for ($x=0;$x<$numCampos;$x++){
                $z++;            
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue(iif($y,'>=','65',chr($y),'').chr($z)."1", $rs->fieldname($x));
                if(chr($z)=='Z'){
                    $y++;
                    $z=64;
                }
                        
        }
        
        while ($rs->getrow()) {
            $y=64;
            $z=64;
            for ($x=0;$x<$numCampos;$x++){
                $z++;
                if(strtoupper($rs->fieldname($x))=='DNI' || strtoupper($rs->fieldname($x))=='CHEQUE' || substr(strtoupper($rs->fieldname($x)),0,2)=='S_'){
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit(iif($y,'>=','65',chr($y),'').chr($z).($rs->currrow()+2), $rs->field($x), PHPExcel_Cell_DataType::TYPE_STRING);
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue(iif($y,'>=','65',chr($y),'').chr($z).($rs->currrow()+2), $rs->field($x));
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension(iif($y,'>=','65',chr($y),'').chr($z))->setAutoSize(true);            
                if(chr($z)=='Z'){
                    $y++;
                    $z=64;
                }                
            }
        }
        
    
    //Alineacion
    //$objPHPExcel->getActiveSheet()
    //        ->getStyle('A1:A100')
    //        ->getAlignment()
    //        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle($title_hoja);


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$name_file.xls");
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;