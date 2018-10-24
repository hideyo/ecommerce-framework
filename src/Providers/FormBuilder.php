<?php

namespace Hideyo\Ecommerce\Framework\Providers;

use Html;
use Form;

class FormBuilder extends \Collective\Html\FormBuilder
{
    /**
     * delete ajax button
     * @param  string  $url            
     * @param  string  $buttonLabel    
     * @param  array   $formParameters 
     * @param  array   $buttonOptions  
     * @param  boolean $title          
     * @return object                  
     */
    public function deleteajax($url, $buttonLabel = 'Delete', $formParameters = array(), $buttonOptions = array(), $title = false)
    {
        if (empty($formParameters)) {
            $formParameters = array(
                'method'=>'DELETE',
                'class' =>'delete-form delete-button',
                'url'   =>$url,
                'style' => 'display:inline',
                'data-title' => $title
            );
        } else {
            $formParameters['url'] = $url;
            $formParameters['method'] = 'DELETE';
        };

        return Form::open($formParameters)
            . Form::submit($buttonLabel, $buttonOptions)
            . Form::close();
    }

    /**
     * multi select2 
     * @param  string $name        
     * @param  array  $list        
     * @param  array  $selected    
     * @param  array  $options     
     * @param  string $placeholder 
     * @return string             
     */
    public function multiselect2($name, array $list = [], array $selected = [], $options = [], $placeholder = 'Select one...')
    {
        $options['name'] = $name;
        $html = array();
        if (is_array($selected)) {
            foreach ($selected as $key => $value) {
                      $selected[$value] = $value;
            }
        }

        //dd($list, $selected);
        foreach ($list as $value => $display) {
            $sel = isset($selected[$value])?' selected="selected"':'';
            $html[] = '<option value="'.$value.'"'.$sel.'>'.e($display).'</option>';
        }
     
        // build out a final select statement, which will contain all the values.
        $options = Html::attributes($options);
        $list = "";        
        
        if ($html) {
            $list = implode('', $html);
        }
    
        return "<select multiple {$options} class=\"select2 form-control\">{$list}</select>";
    }

    /**
     * select2
     * @param string $name        
     * @param  array  $list        
     * @param  [type] $selected    
     * @param  array  $options     
     * @param  string $placeholder 
     * @return string             
     */
    public function select2($name, array $list = [], $selected, $options = [], $placeholder = 'Select one...')
    {
        $options['name'] = $name;
        $html = array();

        foreach ($list as $value => $display) {
            $sel = '';
            if ($selected == $value) {
                $sel = ' selected="selected"';
            }
            
            $html[] = '<option value="'.$value.'"'.$sel.'>'.e($display).'</option>';
        }
     
        // build out a final select statement, which will contain all the values.
        $options = HTML::attributes($options);
        $list = "";
        if ($html) {
            $list = implode('', $html);
        }
     
        return "<select {$options} class=\"select2 form-control\">{$list}</select>";
    }
}