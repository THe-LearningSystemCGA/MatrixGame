<?php
class MatrixHelper {
	public $history = [], $size = 0;
	
	function translate($x, $y, $z) {
		$this->history[] = "translate,".$x.",".$y.",".$z;
		$this->size++;
	}
	
	function rotateX($deg) {
		$this->history[] = "rotateX,".$deg;
		$this->size++;
	}
	
	function rotateY($deg) {
		$this->history[] = "rotateY,".$deg;
		$this->size++;
	}
	
	function rotateZ($deg) {
		$this->history[] = "rotateZ,".$deg;
		$this->size++;
	}
}

function degToRad($deg) {
	return ($deg * (pi() / 180));
}

function parseLine($line, $no, &$boxes, &$matrices, &$error_array, &$error_count) {
	$output = "";
	
	// Leere Zeile?
	if(ctype_space($line) || empty($line)) {
		// nichts
		return null;
	}

	// Kommentar?
	$result = preg_match("/^\/\/(.*)/", $line);
	if($result == 1) {
		// nichts
		return null;
	}

	// new Box
	$result = preg_match("/^\s*(.+\S)\s*=\s*new Box\s*\(\s*(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		$boxes[] = $values[1];
		return $output .= $values[1]." = new Physijs.BoxMesh( new THREE.BoxGeometry(".$values[2].", ".$values[3].", ".$values[4]."), holz_material, 0);\n".$values[1].".receiveShadow = true;\n".$values[1].".castShadow = true;\n";
	}

	/*// Position
	$result = preg_match("/^\s*(.+\S).position\s*\(\s*(-?\d+\.?\d*),\s*(-?\d+\.?\d*),\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		if(in_array($values[1], $boxes)) {
			return $values[1].".position.set(".$values[2].", ".$values[3].", ".$values[4].");\n";
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}

	// Rotate
	$result = preg_match("/^\s*(.+\S).rotate\s*\(\s*(-?\d+\.?\d*),\s*(-?\d+\.?\d*),\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		if(in_array($values[1], $boxes)) {
			return $values[1].".rotateX(".degToRad($values[2]).");\n".$values[1].".rotateY(".degToRad($values[3]).")\n".$values[1].".rotateZ(".degToRad($values[4]).");\n";
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}*/
	
	// new Matrix4f
	$result = preg_match("/^\s*(.+\S)\s*=\s*new Matrix4f\s*\(\s*\);/", $line, $values);
	if($result == 1) {
		$matrices[$values[1]] = new MatrixHelper();
		return null;
	}
	
	// Translate Matrix
	$result = preg_match("/^\s*(.+\S).translate\s*\(\s*(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		if(is_a($matrices[$values[1]], "MatrixHelper")) {
			$matrices[$values[1]]->translate($values[2], $values[3], $values[4]);
			return null;
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}
	
	// RotateX Matrix
	$result = preg_match("/^\s*(.+\S).rotateX\s*\(\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		if(is_a($matrices[$values[1]], "MatrixHelper")) {
			$matrices[$values[1]]->rotateX($values[2]);
			return null;
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}
	
	// RotateY Matrix
	$result = preg_match("/^\s*(.+\S).rotateY\s*\(\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		if(is_a($matrices[$values[1]], "MatrixHelper")) {
			$matrices[$values[1]]->rotateY($values[2]);
			return null;
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}
	
	// RotateZ Matrix
	$result = preg_match("/^\s*(.+\S).rotateZ\s*\(\s*(-?\d+\.?\d*)\s*\);/", $line, $values);
	if($result == 1) {
		if(is_a($matrices[$values[1]], "MatrixHelper")) {
			$matrices[$values[1]]->rotateZ($values[2]);
			return null;
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}
	
	// Set Matrix
	$result = preg_match("/^\s*(.+\S).setModelmatrix\s*\(\s*(.+\S)\s*\);/", $line, $values);
	if($result == 1) {
		if(in_array($values[1], $boxes)) {
			if(is_a($matrices[$values[2]], "MatrixHelper")) {
				$output  = "var ".$values[2]." = new THREE.Matrix4();\n";
				for ($i = 0; $i < $matrices[$values[2]]->size; $i++) {
					$parts = explode(",", $matrices[$values[2]]->history[$i]);
					if ($parts[0] == "rotateX") {
						$output .= $values[2]."rotX = new THREE.Matrix4();\n";
						$output .= $values[2]."rotX.makeRotationX(".degToRad($parts[1]).");\n";
						$output .= $values[2].".multiplyMatrices(".$values[2].", ".$values[2]."rotX);\n";
					}
					if ($parts[0] == "rotateY") {
						$output .= $values[2]."rotY = new THREE.Matrix4();\n";
						$output .= $values[2]."rotY.makeRotationY(".degToRad($parts[1]).");\n";
						$output .= $values[2].".multiplyMatrices(".$values[2].", ".$values[2]."rotY);\n";
					}
					if ($parts[0] == "rotateZ") {
						$output .= $values[2]."rotZ = new THREE.Matrix4();\n";
						$output .= $values[2]."rotZ.makeRotationZ(".degToRad($parts[1]).");\n";
						$output .= $values[2].".multiplyMatrices(".$values[2].", ".$values[2]."rotZ);\n";
					}
					if ($parts[0] == "translate") {
						$output .= $values[2]."trans = new THREE.Matrix4();\n";
						$output .= $values[2]."trans.makeTranslation(".$parts[1].", ".$parts[2].", ".$parts[3].");\n";
						$output .= $values[2].".multiplyMatrices(".$values[2].", ".$values[2]."trans);\n";
					}
				}
				
				$output .= $values[1].".matrixAutoUpdate = false;\n";
				$output .= $values[1].".matrix.identity();\n";
				$output .= $values[1].".applyMatrix(".$values[2].");\n";
				
				return $output;
			}
			else {
				$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[2]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
				$error_count++;
				return null;
			}
		}
		else {
			$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"\\\"".$values[1]."\\\" unkown (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
			$error_count++;
			return null;
		}
	}

	// Wird nur aufgerufen, falls keine RegEx gegriffen hat -> Fehler
	$error_array[] = "{\n\"line\": ".($no + 1).",\n\"error\": \"unknown function or bad syntax (".str_replace(array("\r", "\n"), '', htmlentities($line)).")\"\n}";
	$error_count++;
}

function parseCode($code, &$errors) {
	$start = microtime(true);
	$output = "";
	$boxes = [];
	$matrices = [];
	$lines = explode("\n", $code);
	$error_count = [];
	$error_count = 0;
	
	foreach ($lines as $no => $line) {
		if(!ctype_space($line) && !empty($line)) {
			// Mehrere Befehle in einer Zeile abfangen
			$exploded = preg_split("@(?<=;)@", $line);
			$len = sizeof($exploded);
			for($i = 0; $i < $len; $i++) {
				if(!ctype_space($exploded[$i])) {
					$output .= parseLine($exploded[$i], $no, $boxes, $matrices, $error_array, $error_count);
					//echo "Zeile $no, Durchlauf $i (".strlen($exploded[$i])."): ";
					//var_dump($exploded[$i]);
					//echo "\n";
				}
			}
		}
	}
	
	// Boxen zur Scene hinzfügen
	foreach ($boxes as $box) {
		$output .= "scene.add(".$box.");\n";
	}
	
	// Fehler in JSON umwandeln
	if($error_count > 0) {
		$errors = "{\n\"error_count\": ".$error_count.",\n\"errors\":\n[ ";
		for ($i = 0; $i <= $error_count; $i++) {
			$errors .= $error_array[$i];
			if(($i+1) < $error_count) {
				$errors .= ", ";
			}
		}
		$errors .= "\n]\n}";
	}
	
	// Ausführungszeit als Kommentar anhängen
	$output .= "// Parsed in ".round(((microtime(true)-$start)*1000),3)."ms\n";
	
	return $output;
}

/*$code = "bla = new Box(30, 20, 10);
bla.position(1, 2, 3);
bla.rotate(20, 30,34);";
echo nl2br(parseCode($code, $errors));
echo $errors;*/
?>