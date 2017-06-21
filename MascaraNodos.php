<?php

$datos_json = '{"id_transaccion":0,"cuenta":"demo","user":"administrador","password":"Administr4dor","getPdf":true,"conceptos":[{"ClaveProdServ":"01010101","ClaveUnidad":"F52","Importe":"2250000","unidad":"TONELADA","noIdentificacion":"00001","cantidad":"1.5","descripcion":"ACERO","valorUnitario":"1500000","impuesto":"240000","porcentaje_imp":"16","Impuestos":{"Traslados":[{"Base":"2250000","Impuesto":"001","TipoFactor":"Tasa","TasaOCuota":"0.160000","Importe":"360000"}],"Retenciones":[{"Base":"2250000","Impuesto":"002","TipoFactor":"Tasa","TasaOCuota":"0.530000","Importe":"247500"}]},"CuentaPredial":{"Numero":"51888"}},{"ClaveProdServ":"required","ClaveUnidad":"required","Importe":"required","noIdentificacion":"766115","unidad":"Kg","cantidad":"50","descripcion":"Cebolla Blanca","valorUnitario":"10","impuesto":"0","porcentaje_imp":"0","Impuestos":{"Traslados":[{"Base":"2400","Impuesto":"001","TipoFactor":"Tasa","TasaOCuota":"0.160000","Importe":"384"}],"Retenciones":[{"Base":"2400","Impuesto":"002","TipoFactor":"Tasa","TasaOCuota":"0.530000","Importe":"264"}]},"InformacionAduanera":[{"NumeroPedimento":"15  48  4567  6001234"}]},{"ClaveProdServ":"01010101","ClaveUnidad":"F52","Importe":"2250000","unidad":"TONELADA","noIdentificacion":"00001","cantidad":"1.5","descripcion":"ACERO","valorUnitario":"1500000","impuesto":"240000","porcentaje_imp":"16","Impuestos":{"Trasladoss":[{"Base":"17000","Impuesto":"001","TipoFactor":"Tasa","TasaOCuota":"0.530000","Importe":"2720"}],"Retenciones":[{"Base":"17000","Impuesto":"002","TipoFactor":"Tasa","TasaOCuota":"0.160000","Importe":"1870"}]},"Parte":[{"ClaveProdServ":"01010101","NoIdentificacion":"055155","Cantidad":"1.0","Descripcion":"PARTE EJEMPLO","Unidad":"UNIDAD","ValorUnitario":"1.00","Importe":"1.00","InformacionAduanera":{"NumeroPedimento":"15  48  4567  6981235"}}]}],"datos_factura":{"Version":"3.3","FormaPago":"03","TipoCambio":"1.0","Confirmacion":"optional","Descuento":"0.00","MetodoPago":"PUE","RegimenFiscal":"601","LugarExpedicion":"22010","Moneda":"MXN","TipoDeComprobante":"0","NumCtaPago":"1245","CondicionesDePago":"Crédito","comentarios":"Probando generacion de CFDI","numero_de_pago":"1","cantidad_de_pagos":"1","ret_iva":"100","ret_isr":"200","no_sucural":"1","CfdiRelacionados":{"TipoRelacion":"01","CfdiRelacionado":{"UUID":"A39DA66B-52CA-49E3-879B-5C05185B0EF7"}},"Impuestos":{"TotalImpuestosRetenidos":"247500","TotalImpuestosTrasladados":"360000","Retenciones":[{"Impuesto":"002","Importe":"247500"}],"Traslados":[{"Impuesto":"001","TipoFactor":"Tasa","TasaOCuota":"0.160000","Importe":"360000"}]}},"method":"nueva_factura","cliente":{"id":"189","ResidenciaFiscal":"MEX","NumRegIdTrib":"0000000000000","UsoCFDI":"G01","nombre":"NUEVA EMPRESA DE EJEMPLO","rfc":"EEA9709083R7"}}';
$masc_json = '{"_tipo":"obj","_elems":[{"conceptos":{"_tipo":"arr","_atribs":["ClaveProdServ","ClaveUnidad","impuesto","porcentaje_imp"],"_elems":[{"Impuestos":{"_tipo":"obj","_elems":[{"Traslados":{"_tipo":"arr","_atribs":["Base","Impuesto","TipoFactor","TasaOCuota","Importe"]}},{"Retenciones":{"_tipo":"arr","_atribs":["Base","Impuesto","TipoFactor","TasaOCuota","Importe"]}}]}},{"CuentaPredial":{"_tipo":"obj","_atribs":["Numero"]}},{"InformacionAduanera":{"_tipo":"arr","_atribs":["NumeroPedimento"]}},{"Parte":{"_tipo":"arr","_atribs":["ClaveProdServ","NoIdentificacion","Cantidad","Descripcion","Unidad","ValorUnitario","Importe"],"_elems":[{"InformacionAduanera":{"_tipo":"obj","_atribs":["NumeroPedimento"]}}]}}]}},{"datos_factura":{"_tipo":"obj","_atribs":["Confirmacion","NumCtaPago","comentarios","numero_de_pago","cantidad_de_pagos","ret_iva","ret_isr","no_sucural"],"_elems":[{"CfdiRelacionados":{"_tipo":"obj","_atribs":["TipoRelacion"],"_elems":[{"CfdiRelacionado":{"_tipo":"obj","_atribs":["UUID"]}}]}},{"Impuestos":{"_tipo":"obj","_elems":[{"Traslados":{"_tipo":"arr","_atribs":["TipoFactor"]}}]}}]}},{"cliente":{"_tipo":"obj","_atribs":["ResidenciaFiscal","NumRegIdTrib","UsoCFDI"]}}]}';

$datos = json_decode($datos_json);
$masc = json_decode($masc_json);

$datos_extra = extraerMascara($datos, $masc);
file_put_contents('datos_extra.json', json_encode($datos_extra));

generarDom(key($datos_extra), $datos_extra);

function generarDom($campo, $dato)
{
    if(is_array($dato))
    {
        foreach($dato as $elem)
        {
            $campo_temp = $campo;
            $campo_temp = preg_replace('/as$/', 'a', $campo_temp);
            $campo_temp = preg_replace('/os$/', 'o', $campo_temp);
            $campo_temp = preg_replace('/ones$/', 'ón', $campo_temp);
            $campo_temp = preg_replace('/es$/', '', $campo_temp);
            $campo_temp = preg_replace('/s$/', '', $campo_temp);
            generarDom($campo_temp, $elem);
        }
    }
    else if(is_object($dato))
    {
        foreach($dato as $llave => $valor)
        {
            generarDom($llave, $valor);
        }
    }
    else
    {
        echo $campo;
    }
}

function crear_elemento($cfd, $parent, $name)
{
	$element = $cfd->createElement($name);
	$parent->appendChild($element);
	return $element;
}

function crear_atributo($cfd, $element, $name, $value)
{
	$new_attribute = $cfd->createAttribute($name);
	$element->appendChild($new_attribute);
	$attribute_value = $cfd->createTextNode($value);
	$new_attribute->appendChild($attribute_value);
}

function extraerMascara($datos, $masc)
{
    if($masc->_tipo == 'obj')
    {
        $elem = $datos;
        
        return llenarDatos($masc, $elem);
    }
    
    if($masc->_tipo == 'arr')
    {
        $arr = [];
        
        if(is_array($datos))
        {
            foreach($datos as $elem)
            {
                array_push($arr, llenarDatos($masc, $elem));
            }
        }
        else
        {
            array_push($arr, llenarDatos($masc, $datos));
        }
        
        return $arr;
    }
}

function llenarDatos($masc, $elem)
{
    $obj = new stdClass();
    
    if(isset($masc->_atribs))
    {
        foreach($masc->_atribs as $llave)
        {
            if(isset($elem->$llave))
            {
                $obj->$llave = $elem->$llave;
            }
        }
    }
    
    if(isset($masc->_elems))
    {
        foreach($masc->_elems as $subnodo)
        {
            $llave = key($subnodo);
            
            if(isset($elem->$llave))
            {
                $subelem = $elem->$llave;
                $obj->$llave = extraerMascara($subelem, $subnodo->$llave);
            }
        }
    }
    
    return $obj;
}
