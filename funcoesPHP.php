<?php
	function muda_data_en($data) {
	    if (!is_string($data) || empty($data)) {
	        return 'Formato de data inválido';
	    }

	    if (strpos($data, '/') !== false) {
	        $aux = explode('/', $data);
	        
	        if (count($aux) === 3) {
	            $c = array_reverse($aux);
	            $data = implode('-', $c); 
	        } else {
	            $data = 'Data inválida';
	        }
	    } else {
	        $data = 'Formato de data inválido';
	    }	    
	    return $data;
	}

	function muda_data_pt($data) {
	    // Se a data estiver vazia ou for nula, retorna 00/00/0000
	    if (empty($data) || trim($data) === '') {
	        return '00/00/0000';
	    }
	
	    // Lista de formatos aceitos
	    $formatos = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y', 'm-d-Y'];
	
	    foreach ($formatos as $formato) {
	        $data_formatada = DateTime::createFromFormat($formato, $data);
	
	        // Valida se a data foi criada corretamente
	        if ($data_formatada !== false && $data_formatada->format($formato) === $data) {
	            return $data_formatada->format('d/m/Y');
	        }
	    }
	
	    // Se nenhum formato for válido, retorna 00/00/0000
	    return '00/00/0000';
	}