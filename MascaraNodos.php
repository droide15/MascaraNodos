<?php

$original = '{"id_transaccion":0,"cuenta":"demo","user":"administrador","password":"Administr4dor","getPdf":true,"conceptos":[{"ClaveProdServ":"01010101","ClaveUnidad":"F52","Importe":"2250000","unidad":"TONELADA","Impuestos":{"Traslados":[{"Base":"2250000","Impuesto":"001"}],"Retenciones":[{"Base":"2250000","Impuesto":"002"}]},"CuentaPredial":{"Numero":"51888"}},{"ClaveProdServ":"required","ClaveUnidad":"required","Importe":"required","noIdentificacion":"766115","unidad":"Kg","Impuestos":{"Traslados":[{"Base":"2400","Impuesto":"001"}],"Retenciones":[{"Base":"2400","Impuesto":"002"}]},"InformacionAduanera":[{"NumeroPedimento":"15  48  4567  6001234"}]},{"ClaveProdServ":"01010101","ClaveUnidad":"F52","Importe":"2250000","unidad":"TONELADA","Impuestos":{"Traslados":[{"Base":"17000","Impuesto":"001"}],"Retenciones":[{"Base":"17000","Impuesto":"002"}]},"Parte":[{"ClaveProdServ":"01010101","NoIdentificacion":"055155","Cantidad":"1.0","Descripcion":"PARTE EJEMPLO","InformacionAduanera":{"NumeroPedimento":"15  48  4567  6981235"}}]}],"datos_factura":{"Version":"3.3","FormaPago":"03","Confirmacion":"optional","NumCtaPago":"1245","CfdiRelacionados":{"TipoRelacion":"01","CfdiRelacionado":{"UUID":"A39DA66B-52CA-49E3-879B-5C05185B0EF7"}},"Impuestos":{"TotalImpuestosRetenidos":"247500","TotalImpuestosTrasladados":"360000","Retenciones":[{"Impuesto":"002","Importe":"247500"}],"Traslados":[{"Impuesto":"001","TipoFactor":"Tasa"}]}},"method":"nueva_factura","cliente":{"id":"189","ResidenciaFiscal":"MEX","UsoCFDI":"G01","rfc":"EEA9709083R7"}}';
$masc_json = '{"_tipo":"obj","_elems":[{"conceptos":{"_tipo":"arr","_atribs":["ClaveProdServ","ClaveUnidad"],"_elems":[{"Impuestos":{"_tipo":"obj","_elems":[{"Traslados":{"_tipo":"arr","_atribs":["Base","Impuesto"]}},{"Retenciones":{"_tipo":"arr","_atribs":["Base","Impuesto"]}}]}},{"CuentaPredial":{"_tipo":"obj","_atribs":["Numero"]}},{"InformacionAduanera":{"_tipo":"arr","_atribs":["NumeroPedimento"]}},{"Parte":{"_tipo":"arr","_atribs":["ClaveProdServ","NoIdentificacion"],"_elems":[{"InformacionAduanera":{"_tipo":"obj","_atribs":["NumeroPedimento"]}}]}}]}},{"datos_factura":{"_tipo":"obj","_atribs":["Confirmacion","NumCtaPago"],"_elems":[{"CfdiRelacionados":{"_tipo":"obj","_atribs":["TipoRelacion"],"_elems":[{"CfdiRelacionado":{"_tipo":"obj","_atribs":["UUID"]}}]}},{"Impuestos":{"_tipo":"obj","_elems":[{"Traslados":{"_tipo":"arr","_atribs":["TipoFactor"]}}]}}]}},{"cliente":{"_tipo":"obj","_atribs":["ResidenciaFiscal","UsoCFDI"]}}]}';
file_put_contents('original.json', $original);

$datos = json_decode($original);
$masc = json_decode($masc_json);

$nodos_masc = extraerMascara($datos, $masc);
file_put_contents('nodos_masc.json', json_encode($nodos_masc));

$parcial_json = '{"id_transaccion":0,"cuenta":"demo","user":"administrador","password":"Administr4dor","getPdf":true,"conceptos":[{"Importe":"2250000","unidad":"TONELADA"},{"Importe":"required","noIdentificacion":"766115","unidad":"Kg"},{"Importe":"2250000","unidad":"TONELADA","Parte":[{"Cantidad":"1.0","Descripcion":"PARTE EJEMPLO"}]}],"datos_factura":{"Version":"3.3","FormaPago":"03","Impuestos":{"TotalImpuestosRetenidos":"247500","TotalImpuestosTrasladados":"360000","Retenciones":[{"Impuesto":"002","Importe":"247500"}],"Traslados":[{"Impuesto":"001"}]}},"method":"nueva_factura","cliente":{"id":"189","rfc":"EEA9709083R7"}}';
$parcial = json_decode($parcial_json);
file_put_contents('parcial.json', $parcial_json);

$completo = combinarNodos($parcial, $nodos_masc);
file_put_contents('completo.json', json_encode($completo));

$completo_dom = new DomDocument('1.0', 'UTF-8');
$prefijo = 'cfdi:';
xml2dom($completo_dom, $completo_dom, $prefijo, 'Comprobante', $completo);
file_put_contents('completo.xml', $completo_dom->saveXML());

function combinarNodos($parcial, $nodos_masc){
    if(is_array($nodos_masc))
    {
        $arr = [];
        
        foreach($nodos_masc as $llave => $valor)
        {
            array_push($arr, combinarNodos($parcial[$llave], $valor));
        }
        
        return $arr;
    }
    else if(is_object($nodos_masc))
    {
        $completo = new stdClass();
        
        foreach($parcial as $llave => $valor)
        {
            if(!isset($nodos_masc->$llave)){
                $completo->$llave = $valor;
            }
        }
        
        foreach($nodos_masc as $llave => $valor)
        {
            if(isset($parcial->$llave)){
                $completo->$llave = combinarNodos($parcial->$llave, $valor);
            }
            else {
                $completo->$llave = $valor;
            }
        }
        
        return $completo;
    }
}

function xml2dom($doc_dom, $elem_dom, $prefijo, $campo, $dato)
{
    if(is_array($dato))
    {
        $campo_temp = $campo;
        $campo_temp = preg_replace('/as$/', 'a', $campo_temp);
        $campo_temp = preg_replace('/os$/', 'o', $campo_temp);
        $campo_temp = preg_replace('/ones$/', 'ón', $campo_temp);
        $campo_temp = preg_replace('/es$/', '', $campo_temp);
        $campo_temp = preg_replace('/s$/', '', $campo_temp);
        if (preg_match('/s$/', $campo))
            $nuevo_elem = crear_elemento($doc_dom, $elem_dom, $prefijo.$campo);
        else
            $nuevo_elem = $elem_dom;
        foreach($dato as $elem)
        {
            xml2dom($doc_dom, $nuevo_elem, $prefijo, $campo_temp, $elem);
        }
    }
    else if(is_object($dato))
    {
        $nuevo_elem = crear_elemento($doc_dom, $elem_dom, $prefijo.$campo);
        foreach($dato as $llave => $valor)
        {
            xml2dom($doc_dom, $nuevo_elem, $prefijo, $llave, $valor);
        }
    }
    else
    {
        crear_atributo($doc_dom, $elem_dom, $campo, $dato);
    }
}

function crear_elemento($doc_dom, $elem_dom, $nombre)
{
	$nuevo_elem = $doc_dom->createElement($nombre);
	$elem_dom->appendChild($nuevo_elem);
	return $nuevo_elem;
}

function crear_atributo($doc_dom, $elem_dom, $campo, $valor)
{
	$atributo = $doc_dom->createAttribute($campo);
	$elem_dom->appendChild($atributo);
	$attribute_value = $doc_dom->createTextNode($valor);
	$atributo->appendChild($attribute_value);
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
