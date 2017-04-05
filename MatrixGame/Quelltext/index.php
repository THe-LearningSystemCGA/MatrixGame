<?php include("parser.php");

if(isset($_POST['textarea_code']) && isset($_POST['level'])) {
	setcookie($_POST['level'], gzdeflate($_POST['textarea_code'], 9), time()+365*24*60*60);
	$textarea_code = $_POST['textarea_code'];
	$js_code = parseCode($textarea_code, $errors);
}
else if(isset($_POST['level_selector']) && isset($_COOKIE[$_POST['level_selector']])) {
	$textarea_code = gzinflate($_COOKIE[$_POST['level_selector']]);
	$js_code = parseCode($textarea_code, $errors);
}
else if (isset($_POST['level_selector']) && !isset($_COOKIE[$_POST['level_selector']])) {
	if($_POST['level_selector'] == "beispiel") {
		$textarea_code = file_get_contents("levels/beispiel_code.txt");
		$js_code = parseCode($textarea_code, $errors);
	}
	else {
		$textarea_code = "";
		$js_code = "";
	}
}
else { // Beispiel
	if (isset($_COOKIE["beispiel"])) {
		$textarea_code = gzinflate($_COOKIE["beispiel"]);
		$js_code = parseCode($textarea_code, $errors);
	}
	else {
		$textarea_code = file_get_contents("levels/beispiel_code.txt");
		$js_code = parseCode($textarea_code, $errors);
	}
}
?>
<html>
	<head>
		<title>Murmelspiel - CGA Lernanwendung</title>
		<link rel="stylesheet" href="style.css">
		<script type="text/javascript" src="js/threejs/build/three.js"></script>
        <script type="text/javascript" src="js/physijs/physi.js"></script>
        <script type="text/javascript" src="js/threejs/OrbitControls.js"></script>
		<script type="text/javascript" src="js/ace/src-min/ace.js"></script>
	</head>
	<body>
	<div id="wrapper">
		<div id="header">
            <img src="img/logo_THKoeln.png" style="float:right" />
            <h1 style="font-size:24px; font-weight:400; margin-bottom:0">CGA Lernanwendung</h1>
            <h2 style="color:#666; font-size:38px; font-weight:400; margin-bottom:0">Murmelspiel</h2>
        </div>

        <div id="levelselect">
			<form method="post" type="post" id="level_form" style="float: right;">
				<?php if((!isset($_POST['level_selector']) && !isset($_POST['level'])) || $_POST['level_selector'] == "beispiel" || $_POST['level'] == "beispiel") { ?><input type="button" style="font-size: 1.2em; margin-right: 10px; margin-top: 10px; margin-bottom: 5px;" onclick="resetExampleCookie()" value="Zurücksetzen" /><?php } ?>
				<select name="level_selector" style="font-size: 1.2em; float: right; margin-right: 10px; margin-top: 10px; margin-bottom: 5px;" onchange="document.getElementById('level_form').submit();">
						<option value="beispiel" <?php echo (($_POST['level'] == "beispiel" || $_POST['level_selector'] == "beispiel") ? "selected" : "") ?>>Beispiel</option>
						<?php
							$levels = parse_ini_file("levels.ini", TRUE);
							foreach ($levels as $name => $level) {
								$output = "<option value=\"".$name."\" ";
								if($_POST['level'] == $name || $_POST['level_selector'] == $name) {
									$output .= "selected";
								}
								$output .= ">".$level['name']."</option>\n";
								echo $output;
							}
						?>
				</select>
			</form>
		</div>

		<div id="view">
			<div id="viewport"></div>
			<div id="zielErreichtDiv"></div>

			<script type="text/javascript">

				Physijs.scripts.worker = 'js/physijs/physijs_worker.js';
				Physijs.scripts.ammo = 'examples/js/ammo.js';


						renderer = new THREE.WebGLRenderer({antialias: true});
						renderer.setSize( document.getElementById("view").offsetWidth, document.getElementById("view").offsetHeight );
						renderer.shadowMap.enabled = true;
						renderer.shadowMap.type = THREE.PCFSoftShadowMap;
						document.getElementById( 'viewport' ).appendChild( renderer.domElement );

						scene = new Physijs.Scene;
						scene.setGravity ( new THREE.Vector3( 0, -80, 0 ) );

						camera = new THREE.PerspectiveCamera(
							45,
							document.getElementById("view").offsetWidth / document.getElementById("view").offsetHeight,
							1,
							1000
						);
						camera.position.set( 40, 20, -30 );
						
						scene.add( camera );

						<?php
						if(!(!isset($_POST['lookAtMurmel']) || $_POST['lookAtMurmel'] == "true")) {
							echo "controls = new THREE.OrbitControls( camera, renderer.domElement );";
						}
						?>

						//Grid
						function drawGrid(size, steps) {

							var geoX1 = new THREE.Geometry();
							geoX1.vertices.push(new THREE.Vector3(0, 0, 0));
							geoX1.vertices.push(new THREE.Vector3(size, 0, 0));

							var geoX2 = new THREE.Geometry();
							geoX2.vertices.push(new THREE.Vector3(-size, 0, 0));
							geoX2.vertices.push(new THREE.Vector3(0, 0, 0));


							var geoY1 = new THREE.Geometry();
							geoY1.vertices.push(new THREE.Vector3(0, 0, 0));
							geoY1.vertices.push(new THREE.Vector3(0, size, 0));

							var geoY2 = new THREE.Geometry();
							geoY2.vertices.push(new THREE.Vector3(0, -size, 0));
							geoY2.vertices.push(new THREE.Vector3(0, 0, 0));

							var geoZ1 = new THREE.Geometry();
							geoZ1.vertices.push(new THREE.Vector3(0, 0, 0));
							geoZ1.vertices.push(new THREE.Vector3(0, 0, size));

							var geoZ2 = new THREE.Geometry();
							geoZ2.vertices.push(new THREE.Vector3(0, 0, -size));
							geoZ2.vertices.push(new THREE.Vector3(0, 0, 0));


							var matX1 = new THREE.LineBasicMaterial({ color: 0xff0000 });
							var matX2 = new THREE.LineBasicMaterial({ color: 0x700000 });

							var matY1 = new THREE.LineBasicMaterial({ color: 0x00ff00 });
							var matY2 = new THREE.LineBasicMaterial({ color: 0x007000 });

							var matZ1 = new THREE.LineBasicMaterial({ color: 0x0000ff });
							var matZ2 = new THREE.LineBasicMaterial({ color: 0x000070 });

							var lineX1 = new THREE.Line(geoX1, matX1);
							var lineX2 = new THREE.Line(geoX2, matX2);
							var lineY1 = new THREE.Line(geoY1, matY1);
							var lineY2 = new THREE.Line(geoY2, matY2);
							var lineZ1 = new THREE.Line(geoZ1, matZ1);
							var lineZ2 = new THREE.Line(geoZ2, matZ2);
							scene.add(lineX1);
							scene.add(lineX2);
							scene.add(lineY1);
							scene.add(lineY2);
							scene.add(lineZ1);
							scene.add(lineZ2);

							var matG = new THREE.LineBasicMaterial({ color: 0x404040 });
							for (i = -size; i <= size; i=i+steps) {
								if(i != 0) {
									var geo1 = new THREE.Geometry();
									geo1.vertices.push(new THREE.Vector3(-size, 0, i));
									geo1.vertices.push(new THREE.Vector3(size, 0, i));

									var geo2 = new THREE.Geometry();
									geo2.vertices.push(new THREE.Vector3(i, 0, -size));
									geo2.vertices.push(new THREE.Vector3(i, 0, size));

									var geo3 = new THREE.Geometry();
									geo3.vertices.push(new THREE.Vector3(0.5, i, 0));
									geo3.vertices.push(new THREE.Vector3(-0.5, i, 0));

									var geo4 = new THREE.Geometry();
									geo4.vertices.push(new THREE.Vector3(0, i, 0.5));
									geo4.vertices.push(new THREE.Vector3(0, i, -0.5));

									var line1 = new THREE.Line(geo1, matG);
									var line2 = new THREE.Line(geo2, matG);
									var line3 = new THREE.Line(geo3, matY2);
									var line4 = new THREE.Line(geo4, matY2);
									scene.add(line1);
									scene.add(line2);
									scene.add(line3);
									scene.add(line4);
								}
							}
						}

						drawGrid(100, 1);

						//skybox
						var skyBoxImgPath = "img/skybox/";
						var skyBoxImg  = ["px.png", "nx.png", "py.png", "ny.png", "pz.png", "nz.png"];

						var skyBoxMat = [];
						for (var i = 0; i < 6; i++)
							skyBoxMat.push( new THREE.MeshBasicMaterial({
								map: THREE.ImageUtils.loadTexture( skyBoxImgPath + skyBoxImg[i] ),
								side: THREE.BackSide
							}));

						var skyBoxCube = new THREE.CubeGeometry( 500, 500, 500 );
						var skyBoxMaterial = new THREE.MeshFaceMaterial( skyBoxMat );
						var skyBox = new THREE.Mesh( skyBoxCube, skyBoxMaterial );
						scene.add( skyBox );

						// Light
						ambientLight = new THREE.AmbientLight (0xffffff, 0.8);
						ambientLight.intensity = 0.2;
						scene.add(ambientLight);

						spotLight = new THREE.SpotLight (0xffefdb);
						spotLight.position.set(20,50,0);
						spotLight.castShadow = true;
						spotLight.intensity = 0.7;

						spotLight.shadow.mapSize.width = 2048;
						spotLight.shadow.mapSize.height = 2048;
						scene.add(spotLight);

						spotLight2 = new THREE.SpotLight (0xffefdb);
						spotLight2.position.set(-20,50,-100);
						spotLight2.castShadow = true;
						spotLight2.intensity = 0.5;

						spotLight2.shadow.mapSize.width = 2048;
						spotLight2.shadow.mapSize.height = 2048;
						scene.add(spotLight2);

						// Loader
						loader = new THREE.TextureLoader();

						// Textures
						holz_material = Physijs.createMaterial(
							new THREE.MeshPhongMaterial({ map: loader.load( 'img/texture/wood-floor.jpg' ), refractionRatio: 0, reflectivity: 0.1 })
						);
						holz_material.map.wrapS = holz_material.map.wrapT = THREE.RepeatWrapping;
						holz_material.map.repeat.set( 1, 1 );

						stein_material = Physijs.createMaterial(
							new THREE.MeshPhongMaterial({ map: loader.load( 'img/texture/stone.jpg' ), refractionRatio: 0, reflectivity: 0.1 })
						);
						stein_material.map.wrapS = stein_material.map.wrapT = THREE.RepeatWrapping;
						stein_material.map.repeat.set( 1, 1 );

						murmel_material = Physijs.createMaterial(
							new THREE.MeshLambertMaterial({ map: loader.load( 'img/texture/murmel.jpg' ), refractionRatio: 1, opacity: 0.95, transparent: true, reflectivity: 2})
						);
						murmel_material.map.wrapS = murmel_material.map.wrapT = THREE.RepeatWrapping;
						murmel_material.map.repeat.set( 1, 1 );

						<?php
						$errors = json_decode($errors, true);

						echo $js_code;

						if(isset($_POST['level'])) {
							include("levels/".$_POST['level'].".txt");
						}
						else if(isset($_POST['level_selector'])) {
							include("levels/".$_POST['level_selector'].".txt");
						}
						else {
							include("levels/beispiel.txt");
						}
						?>



						function distance(p, q) {
							return Math.sqrt(Math.pow((p.position.x - q.position.x), 2) + Math.pow((p.position.y - q.position.y), 2) + Math.pow((p.position.z - q.position.z), 2));
						}

						function kugelImZiel(kugel, ziel, zielradius) {
							if(distance(kugel, ziel) < zielradius) {
								return true;
							}
							return false;
						}

						function zielErreicht(timer) {
							clearTimeout(imZielInterval);
							document.getElementById('zielErreichtDiv').innerHTML = "Ziel erreicht!";
							secVerbleibend = 3;
							zielLight.color.setHex(0x00ff00);
							zielLight.intensity = 0.5;
						}

						var imZielTimer = 0;
						var imZielInterval = 0;
						var secVerbleibend = 3;
						var zielLightBack = false;
						render = function() {
							scene.simulate(); // run physics
							<?php
							if(!isset($_POST['lookAtMurmel']) || $_POST['lookAtMurmel'] == "true") {
								echo "camera.lookAt(kugel.position);";
							}
							?>

							imZiel = kugelImZiel(kugel, ziel, zielradius);
							if(imZiel && imZielTimer == 0) {
								imZielTimer = window.setTimeout(function () { zielErreicht(imZielTimer) }, 3000);
								imZielInterval = window.setInterval(function() {
									secVerbleibend -= 0.05;
									document.getElementById('zielErreichtDiv').innerHTML=parseFloat(secVerbleibend).toFixed(2)+"s verbleibend";
									if(zielLight.intensity == 0 && !zielLightBack) {
										zielLight.intensity = 0.1;
									}
									else if(zielLight.intensity == 0.1 && !zielLightBack) {
										zielLight.intensity = 0.2;
									}
									else if(zielLight.intensity == 0.2 && !zielLightBack) {
										zielLight.intensity = 0.3;
									}
									else if(zielLight.intensity == 0.3 && !zielLightBack) {
										zielLight.intensity = 0.4;
									}
									else if(zielLight.intensity == 0.4 && !zielLightBack) {
										zielLight.intensity = 0.5;
										zielLightBack = true;
									}
									else if(zielLight.intensity == 0.1 && zielLightBack) {
										zielLight.intensity = 0;
										zielLightBack = false;
									}
									else if(zielLight.intensity == 0.2 && zielLightBack) {
										zielLight.intensity = 0.1;
									}
									else if(zielLight.intensity == 0.3 && zielLightBack) {
										zielLight.intensity = 0.2;
									}
									else if(zielLight.intensity == 0.4 && zielLightBack) {
										zielLight.intensity = 0.3;
									}
									else if(zielLight.intensity == 0.5 && zielLightBack) {
										zielLight.intensity = 0.4;
									}
								}, 50);
							}
							else if(!imZiel && imZielTimer != 0) {
								clearTimeout(imZielTimer);
								imZielTimer = 0;
								clearTimeout(imZielInterval);
								secVerbleibend = 3;
								document.getElementById('zielErreichtDiv').innerHTML = "";
								zielLight.intensity = 0.0;
								zielLightBack = false;
							}

							if (kugel.position.y < -100 || kugel.position.y > 100) {
								kugel.position.set (murmPosX, murmPosY, murmPosZ);
								kugel.__dirtyPosition = true;
								kugel.rotation.set (0, 0, 0);
								kugel.__dirtyRotation = true;
								kugel.setLinearVelocity(new THREE.Vector3(0, 0, 0));
								kugel.setAngularVelocity(new THREE.Vector3(0, 0, 0));
							}

							renderer.render( scene, camera); // render the scene
							requestAnimationFrame( render );




						};
						requestAnimationFrame( render );

						function restoreCamera(cam) {
							camera.position.set(cam.position.x, cam.position.y, cam.position.z);
							camera.rotation.set(cam.rotation.x, cam.rotation.y, cam.rotation.z);
							controls.target.set(cam.controlCenter.x, cam.controlCenter.y, cam.controlCenter.z);
							controls.update();
						}

						function updateCamForm() {
							var cam = {};
							cam.position = camera.position.clone();
							cam.rotation = camera.rotation.clone();
							cam.controlCenter = controls.target.clone();
							document.getElementById('cameraPos').value = JSON.stringify(cam);
						}

						<?php
						if(!(!isset($_POST['lookAtMurmel']) || $_POST['lookAtMurmel'] == "true")) {
							echo "setInterval(function() { updateCamForm(); }, 500);\n";
							echo "var restorePos = JSON.parse('".$_POST['cameraPos']."');\n";
							echo "restoreCamera(restorePos);\n";
						}
						?>

			</script>

		</div>


		<div id="menue">
			<button class="accordion" id="acc_2_panel">Quelltext</button>
			<div class="panel" style="background-color: #ebebeb;">
				<?php
					if(empty($errors)) {
						echo "<div class=\"full_width\">";
					}
					else {
						echo "<div class=\"half_width\">";
					}
				?>

				<form action="<?=$_SERVER['PHP_SELF']?>" method="post" id="form">
					<button class="button_start" type="submit" style="margin-left:10px; margin-top: 10px; margin-bottom: 5px;">&nbsp;&nbsp;&nbsp;Ausführen&nbsp;&nbsp;&nbsp;</button>
					<input type="radio" name="lookAtMurmel" value="true" <?php if(!isset($_POST['lookAtMurmel']) || $_POST['lookAtMurmel'] == "true") echo "checked"; ?> />Kamera auf Murmel richten
					&nbsp; &nbsp; &nbsp;
					<input type="radio" name="lookAtMurmel" value="false" <?php if($_POST['lookAtMurmel'] == "false") echo "checked"; ?> />Freie Kamera
					<input type="hidden" name="cameraPos" id="cameraPos" value='<?php echo (isset($_POST['cameraPos']) ? $_POST['cameraPos'] : "") ?>' />
					<input type="hidden" name="level" id="level" value="<?php if(isset($_POST['level'])) { echo $_POST['level']; } else if (isset($_POST['level_selector'])) { echo $_POST['level_selector']; } else { echo "beispiel"; } ?>" />
					<textarea class="textarea_code" name="textarea_code" id="textarea_code" style="display:none;"><?=$textarea_code?></textarea>
					<div id="code"><?=$textarea_code?></div>
				</form>
				<div style="width: 100%; height: 10px; background-color: #fff;"> </div>
				</div>
				<?php
					if(!empty($errors)) {
						echo "<div class=\"half_width\" style=\"width: 45%;margin-left: 20px;\">";
						echo "<b><span style=\"color:red;\">".$errors['error_count']." Fehler:</span></b><br /><ul>";
						for ($i = 0; $i < $errors['error_count']; $i++) {
							echo "<li>".$errors['errors'][$i]['line'].": ".$errors['errors'][$i]['error']."</li>";
						}

						echo "</ul></div>";
						echo "<div style=\"clear:both;height: 0;\"></div>";
					}
				?>
			</div>

			<button class="accordion" id="acc_0_panel">Übersicht & Hilfe</button>
			<div class="panel">
				<div id="panel_text">
					<p>Diese Online-Anwendung soll Ihnen helfen, sich im dreidimensionalen Raum (<span style="color: #ff0000;">x</span><span style="color: #009900;">y</span><span style="color: #0000ff;">z</span>) zu orientieren. Ziel des Spiels ist es, eine Murmel von einem festgelegten Startpunkt zum Zielpunkt zu manövrieren. Um dies zu erreichen, müssen Sie die Bahnen einprogrammieren - ganz einfach, oder? Wählen Sie einfach ein Level aus und versuchen Sie Ihr Glück!</p><br />
					<p>Die Kamera befindet sich standardmäßig im Verfolgungsmodus, d.h. die Kamera richtet sich automatisch auf die Murmel. Im freien Kameramodus können Sie die Ansicht mit den folgenden Tasten bewegen:
					<ul>
						<li class="icon iconfirst mouseleft"> Drehen</li>
						<li class="icon mouseright"> Bewegen</li>
						<li class="icon mousewheel"> Zoom</li>
					</ul>
					Die aktuelle Ansicht der freien Kamera wird für die Dauer der Sitzung beibehalten.</p>
				</div>
			</div>

			<button class="accordion" id="acc_1_panel">Befehlsreferenz</button>
			<div class="panel">
				<div id="panel_text">
					<p>Die folgenden Befehle können Sie nutzen, um das Ziel zu erreichen:</p>
					<br />
					<ul class="code">
						<li class="list"><i>box</i> = new Box(x, y, z);<br /><span class="nocode">Erzeugt eine neue Box als <i>box</i> (frei wählbar), mit der angegebenen Größe</span></li>
						<li class="list"><i>box</i>.setModelmatrix(<i>Matrix4f</i>)<br /><span class="nocode">Ändert die Modelmatrix von <i>box</i> auf die Werte der angegebenen <i>Matrix</i></span></li>
						<li class="list"><i>matrix</i> = new Matrix4f();<br /><span class="nocode">Erzeugt eine neue Matrix4f als <i>matrix</i></span></li>
						<li class="list"><i>matrix</i>.translate(x, y, z);<br /><span class="nocode">Multipliziert eine Matrix zur Translation um den Verschiebungsvektor (x,y,z) von rechts an <i>matrix</i></span></li>
						<li class="list"><i>matrix</i>.rotateX(deg);<br /><span class="nocode">Multipliziert eine Matrix zur Rotation um die X-Achse von rechts an <i>matrix</i>. Der Rotationswinkel wird in Grad angegeben</span></li>
						<li class="list"><i>matrix</i>.rotateY(deg);<br /><span class="nocode">Multipliziert eine Matrix zur Rotation um die Y-Achse von rechts an <i>matrix</i>. Der Rotationswinkel wird in Grad angegeben</span></li>
						<li class="list"><i>matrix</i>.rotateZ(deg);<br /><span class="nocode">Multipliziert eine Matrix zur Rotation um die Z-Achse von rechts an <i>matrix</i>. Der Rotationswinkel wird in Grad angegeben</span></li>
					</ul>
				</div>
			</div>


			<script>
				function setCookie(cname, cvalue, exdays) {
				    var d = new Date();
				    d.setTime(d.getTime() + (exdays*24*60*60*1000));
				    var expires = "expires="+ d.toUTCString();
				    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
				}

				function getCookie(cname) {
				    var name = cname + "=";
				    var ca = document.cookie.split(';');
				    for(var i = 0; i <ca.length; i++) {
				        var c = ca[i];
				        while (c.charAt(0)==' ') {
				            c = c.substring(1);
				        }
				        if (c.indexOf(name) == 0) {
				            return c.substring(name.length,c.length);
				        }
				    }
				    return "";
				}

				function resetExampleCookie() {
					document.cookie = "beispiel=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
					window.location = window.location.href;
				}



				// Accordions
				var acc_0_panel = document.getElementById("acc_0_panel");
				var acc_1_panel = document.getElementById("acc_1_panel");
				var acc_2_panel = document.getElementById("acc_2_panel");

				function togglePanel(panel, name, changeState = true) {
					panel.classList.toggle("active");
					panel.nextElementSibling.classList.toggle("show");
					if((getCookie(name) == "" || getCookie(name) == "show") && changeState) {
						setCookie(name, "hide", 365);
					}
					else if(getCookie(name) == "hide" && changeState) {
						setCookie(name, "show", 365);
					}
				}

				acc_0_panel.onclick = function() { togglePanel(acc_0_panel, "acc_0") };
				acc_1_panel.onclick = function() { togglePanel(acc_1_panel, "acc_1") };
				acc_2_panel.onclick = function() { togglePanel(acc_2_panel, "acc_2") };

				if(getCookie("acc_0") == "show" || getCookie("acc_0") == "") {
					togglePanel(acc_0_panel, "acc_0", false);
				}

				if(getCookie("acc_1") == "show" || getCookie("acc_1") == "") {
					togglePanel(acc_1_panel, "acc_1", false);
				}

				if(getCookie("acc_2") == "show" || getCookie("acc_2") == "") {
					togglePanel(acc_2_panel, "acc_2", false);
				}

				// Editor
				var editor = ace.edit("code");
				editor.setTheme("ace/theme/eclipse");
				editor.getSession().setMode("ace/mode/javascript");
				editor.getSession().setValue(document.getElementById('textarea_code').innerHTML);
				editor.setShowPrintMargin(false);
				editor.getSession().on('change', function(){
					document.getElementById('textarea_code').innerHTML = editor.getSession().getValue();
				});

				var Range = ace.require("ace/range").Range;

				<?php
					if(!empty($errors)) {
						for ($i = 0; $i < $errors['error_count']; $i++) {
							echo "editor.session.addMarker(new Range(".($errors['errors'][$i]['line']-1).", 0, ".($errors['errors'][$i]['line']-1).", 1), \"red_line\", \"fullLine\");\n";
						}
					}
				?>
			</script>

		</div>
		<div style="clear:both; height:0;"></div>
		<div id="footer">
			Entwickelt von Andreas Pahlen und Stefan Förster
		</div>
	</div>
	</body>
</html>
