<?php

/**
 *  File: calendar.php | (c) dynarch.com 2004
 *  Distributed as part of "The Coolest DHTML Calendar"
 *  under the same terms.
 *  -----------------------------------------------------------------
 *  This file implements a simple PHP wrapper for the calendar.  It
 *  allows you to easily include all the calendar files and setup the
 *  calendar by instantiating and calling a PHP object.
 */

define('NEWLINE', "\n");

class DHTML_Calendar {
    var $calendar_lib_path;

    var $calendar_file;
    var $calendar_lang_file;
    var $calendar_setup_file;
    var $calendar_theme_file;
    var $calendar_correla;	
    var $calendar_options;

    function __construct($calendar_lib_path = '/calendar/',
                            $lang              = 'en',
                            $theme             = 'calendar-win2k-1',
                            $stripped          = true) {
        if ($stripped) {
            $this->calendar_file = 'calendar_stripped.js';
            $this->calendar_setup_file = 'calendar-setup_stripped.js';
        } else {
            $this->calendar_file = 'calendar.js';
            $this->calendar_setup_file = 'calendar-setup.js';
        }
        $this->calendar_correla=0;
        $this->calendar_lang_file = 'lang/calendar-' . $lang . '.js';
        $this->calendar_theme_file = $theme.'.css';
        $this->calendar_lib_path = preg_replace('/\/+$/', '/', $calendar_lib_path);
        $this->calendar_options = array('ifFormat' => '%d/%m/%Y',
                                        'daFormat' => '%d/%m/%Y');

    }

    function set_option($name, $value) {
        $this->calendar_options[$name] = $value;
    }

    function load_files() {
        echo $this->get_load_files_code();
    }

    function load_files2() {
        return($this->get_load_files_code());
    }

    function get_load_files_code() {
        $code  = ( '<link rel="stylesheet" type="text/css" media="all" href="' .
                   $this->calendar_lib_path . $this->calendar_theme_file .
                   '" />' . NEWLINE );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_file .
                   '"></script>' . NEWLINE );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_lang_file .
                   '"></script>' . NEWLINE );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_setup_file .
                   '"></script>' );
        return $code;
    }

    function _make_calendar_original($other_options = array()) {
        $js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
        $code  = ( '<script type="text/javascript">Calendar.setup({' .
                   $js_options .
                   '});</script>' );
        return $code;
    }

    function make_input_field_original($msjvalid ,$cal_options = array(), $field_attributes = array()) {
        $id = $this->_gen_id();
        $attrstr = $this->_make_html_attr(array_merge($field_attributes,
                                                      array('id'   =>  $msjvalid,
															'size' => 10,
															'maxlength' => 10,
                                                            'type' => 'text')));
        $return='';
		$return.='<input '. $attrstr .'/>';
        $return.='<a href="#" id="'. $this->_trigger_id($id) . '">' .
    	        '<img align="middle" border="0" src="' . $this->calendar_lib_path . 'img.gif" alt="" onClick="Calendar.setup({\'ifFormat\':\'%d/%m/%Y\',\'daFormat\':\'%d/%m/%Y\',\'inputField\':\'Fecha de documento\',\'button\':\'f-calendar-trigger-1\'})" /></a>';
        $options = array_merge($cal_options,
                               array('inputField' => $msjvalid,
                                     'button'     => $this->_trigger_id($id)));
        return $return.$this->_make_calendar($options);
    }

    function _make_calendar($other_options = array()) {
        $js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
        $code  = ( 'Calendar.setup({' .
                   $js_options .
                   '})' );
        return $code;
    }

    function make_input_field($msjvalid ,$cal_options = array(), $field_attributes = array()) { // Esta funci�n se crea para no tener problemas al cargar el calendario en el ajax
        $id = $this->_gen_id();
        $options = array_merge($cal_options,
                               array('inputField' => $msjvalid,
                                     'button'     => $this->_trigger_id($id)));

        $attrstr = $this->_make_html_attr(array_merge($field_attributes,
                                                      array('id'   =>  $msjvalid,
															'size' => 12,
															'maxlength' => 10,
                                                            'type' => 'text')));
        $return='';
		$return.='<input onkeyup=mascara(this,\'/\',patron,true) '. $attrstr .'/>';

		if($this->_read_only($field_attributes)==true){}
		else{
    	    $return.='<a href="#" id="'. $this->_trigger_id($id) . '">' .
	            '<img align="middle" border="0" src="' . $this->calendar_lib_path . 'img.gif" alt="" onClick="'.$this->_make_calendar($options).'" /></a>';
		}
        return $return; 
    }



    /// PRIVATE SECTION

    function _field_id($id) { return 'f-calendar-field-' . $id; }
    function _trigger_id($id) { return 'f-calendar-trigger-' . $id; }
	function  _gen_id() {++$this->calendar_correla; return($this->calendar_correla);}
//  function _gen_id() { static $xid = 0; return ++$xid; }

    function _make_js_hash_original($array) {
        $jstr = '';
        reset($array);
        
        foreach ($array as $key => $val) {
            if (is_bool($val))
                $val = $val ? 'true' : 'false';
            else if (!is_numeric($val))
                $val = '"'.$val.'"';
            if ($jstr) $jstr .= ',';
            $jstr .= '"' . $key . '":' . $val;
        }
                    
        return $jstr;
    }

    function _make_js_hash($array) { // Modificado para que funcione con Ajax
        $jstr = '';
        reset($array);
        foreach ($array as $key => $val) {
           if (is_bool($val))
                $val = $val ? 'true' : 'false';
            else if (!is_numeric($val))
                $val = "'".$val."'";
            if ($jstr) $jstr .= ',';
            $jstr .= "'" . $key . "':" . $val;
        }
        
        return $jstr;
    }

    function _make_html_attr($array) {
        $attrstr = '';
        reset($array);
        foreach ($array as $key => $val) {
           $attrstr .= $key . '="' . $val . '" ';
        }
        return $attrstr;
    }

    function _read_only($array) {
        $readonly = false;
        reset($array);
        foreach ($array as $key => $val) {
           if(strtoupper($key)=='READONLY'){
                $readonly = true;
		break;
            }
        }
        return $readonly;
    }

};