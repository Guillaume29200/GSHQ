<?php
require_once __DIR__ . '/header.php';

$error_message = '';
$success_message = '';
$games = ''; // Par défaut

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form1'])) {
    $valid = true;

    // Validation des champs
    $games 			= trim($_POST['games'] ?? '');
    $adress_ip 		= trim($_POST['adress_ip'] ?? '');
    $c_port 		= trim($_POST['c_port'] ?? '');
    $q_port 		= trim($_POST['q_port'] ?? '');
	$rcon_port 		= trim($_POST['rcon_port'] ?? '');
	$rcon_password 	= $_POST['rcon_password'] ?? '';

    if (empty($games)) {
        $valid = false;
        $error_message .= 'Protocol/Game is required<br>';
    }

    if (empty($adress_ip)) {
        $valid = false;
        $error_message .= 'IP Address is required<br>';
    }
	
	if ($games !== 'discord' && empty($c_port)) {
		$valid = false;
		$error_message .= 'Connection Port is required<br>';
	}

	if ($games !== 'discord' && empty($q_port)) {
		$valid = false;
		$error_message .= 'Connection Port query is required. by default "Your connection port"<br>';
	}

    if ($valid) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_gameserver (games, adress_ip, c_port, q_port, rcon_port, rcon_password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$games, $adress_ip, $c_port, $q_port, $rcon_port, $rcon_password]);

            $success_message = 'Server successfully added';

            // Reset des champs
            $_POST = [];
            $games = '';
        } catch (PDOException $e) {
            $error_message = "Error while adding the server: " . $e->getMessage();
        }
    }
}
?>
	<div class="col-md-12">	
		<div class="card shadow-sm mt-4">
			<div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
				<h5 class="mb-0">🎮 Add new gameserver</h5>
			</div>
			<div class="card-body">
				<div class="col-md-12">
					<?php if (!empty($error_message)): ?>
						<div class="alert alert-danger"><?= $error_message ?></div>
					<?php endif; ?>
					<?php if (!empty($success_message)): ?>
						<div class="alert alert-success"><?= $success_message ?></div>
					<?php endif; ?>
				</div>
				<form action="" method="post">
					<div class="accordion" id="addGameServer">
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingOne">
								<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"aria-expanded="true" aria-controls="collapseOne">🎮 <b>Game and IP Adress</b></button>
							</h2>
							<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#addGameServer">
								<div class="accordion-body">
									<div class="row">
										<div class="col-md-12">				
											<div class="form-group">
												<label for=""><b class="text-muted d-block">Game/Protocol Selection*</b></label>
												<select name="games" id="games_select" class="form-control">
													<option value="" disabled selected>Please choose your game</option>
													<optgroup label="Server valve">
														<option value="cs16" <?php if($games=='cs16') {echo 'selected';} ?>>Counter Strike 1.6</option>
														<option value="cscz" <?php if($games=='cscz') {echo 'selected';} ?>>Counter Strike Condition Zero</option>															
														<option value="css" <?php if($games=='css') {echo 'selected';} ?>>Counter Strike Source</option>
														<option value="csgo" <?php if($games=='csgo') {echo 'selected';} ?>>Counter Strike Global offensive</option>
														<option value="cs2" <?php if($games=='cs2') {echo 'selected';} ?>>Counter Strike 2</option>
														<option value="dod" <?php if($games=='dod') {echo 'selected';} ?>>Day of Defeat 1.3</option>
														<option value="dods" <?php if($games=='dods') {echo 'selected';} ?>>Day of Defeat: Source</option>	
														<option value="gmod" <?php if($games=='gmod') {echo 'selected';} ?>>Garry's Mod</option>
														<option value="hl2mp" <?php if($games=='hl2mp') {echo 'selected';} ?>>Half-life 2 Deatchmatch</option>
														<option value="l4d" <?php if($games=='l4d') {echo 'selected';} ?>>Left 4 Dead</option>
														<option value="l4d2" <?php if($games=='l4d2') {echo 'selected';} ?>>Left 4 Dead 2</option>
														<option value="tf2" <?php if($games=='tf2') {echo 'selected';} ?>>Team fortress 2</option>
													</optgroup>															
													<optgroup label="Server Call of duty">
														<option value="cod2" <?php if($games=='cod2') {echo 'selected';} ?>>Call Of Duty 2</option>
														<option value="cod4" <?php if($games=='cod4') {echo 'selected';} ?>>Call Of Duty 4: Modern Warfare (Cod4X)</option>
													</optgroup>																
													<optgroup label="Other/divers">
														<option value="7dtd" <?php if($games=='7dtd') {echo 'selected';} ?>>7 Days To Die</option>
														<option value="arma3" <?php if($games=='arma3') {echo 'selected';} ?>>Arma 3</option>
														<option value="ase" <?php if($games=='ase') {echo 'selected';} ?>>ARK Survival Evolved</option>
														<option value="bl" <?php if($games=='bl') {echo 'selected';} ?>>Battalion Legacy</option>
														<option value="bms" <?php if($games=='bms') {echo 'selected';} ?>>Black Mesa</option>									
														<option value="fof" <?php if($games=='fof') {echo 'selected';} ?>>Fistful of Frags</option>
														<option value="insurgency" <?php if($games=='insurgency') {echo 'selected';} ?>>Insurgency</option>
														<option value="mohaa" <?php if($games=='mohaa') {echo 'selected';} ?>>Medal of Honor : Allied assault</option>
														<option value="nmrh" <?php if($games=='nmrh') {echo 'selected';} ?>>No More Room in Hell</option>
														<option value="palworld" <?php if($games=='palworld') {echo 'selected';} ?>>Palworld</option>
														<option value="pz" <?php if($games=='pz') {echo 'selected';} ?>>Project Zomboid</option>
														<option value="pvkii" <?php if($games=='pvkii') {echo 'selected';} ?>>Pirates, Vikings and Knights II</option>
														<option value="jk2" <?php if($games=='jk2') {echo 'selected';} ?>>Star Wars - Jedi Knight II</option>
														<option value="squad" <?php if($games=='squad') {echo 'selected';} ?>>Squad</option>
													</optgroup>	
													<optgroup label="Game Protocol">
														<option value="hl1" <?php if($games=='hl1') {echo 'selected';} ?>>Half-life 1 (GoldSRC)</option>
														<option value="hl2" <?php if($games=='hl2') {echo 'selected';} ?>>Half-life 2 (Source)</option>
														<option value="q3" <?php if($games=='q3') {echo 'selected';} ?>>Quake III Engine (id Tech 3)</option>
													</optgroup>	
													<optgroup label="Voice Server">
														<option value="discord" <?php if($games=='discord') {echo 'selected';} ?>>Discord</option>
														<option value="ts3" <?php if($games=='ts3') {echo 'selected';} ?>>Teamspeak 3</option>
													</optgroup>								
												</select>							
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for=""><b class="text-muted d-block">Adress ip *</b></label>
												<input type="text" autocomplete="off" class="form-control" name="adress_ip" value="<?php if(isset($_POST['adress_ip'])){echo $_POST['adress_ip'];} ?>" placeholder="e.g. 192.168.1.50">
											</div>
										</div>
										<div class="col-md-4" id="c_port_container">
											<div class="form-group">
												<label for=""><b class="text-muted d-block">Connection Port *</b></label>
												<input type="text" autocomplete="off" class="form-control" name="c_port" id="c_port" value="<?php if(isset($_POST['c_port'])){echo $_POST['c_port'];} ?>" placeholder="e.g. 27015">
											</div>
										</div>
										<div class="col-md-4" id="q_port_container">
											<div class="form-group">
												<label for=""><b class="text-muted d-block">Default Query Port *</b></label>
												<input type="text" autocomplete="off" class="form-control" name="q_port" id="q_port" value="<?php if(isset($_POST['q_port'])){echo $_POST['q_port'];} ?>" placeholder="e.g. 7777">
											</div>
										</div>										
									</div>
								</div>
							</div>
						</div>
						<div class="accordion-item" id="rcon_info_container">
							<h2 class="accordion-header" id="headingTwo">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">🛡️ <b>Connection informations</b></button>
							</h2>
							<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#addGameServer">
								<div class="accordion-body">
									<div class="row">
										<div class="col-md-12">	
											<div class="alert alert-warning">Some games require your RCON credentials to display your server information. This information is optional and only needed for specific games such as Squad, Battlefield, Minecraft or Palword.</div>
										</div>
										<div class="col-md-6">	
											<div class="form-group">
												<label for=""><b class="text-muted d-block">Rcon Port</b></label>
												<input type="text" autocomplete="off" class="form-control" name="rcon_port" value="<?php if(isset($_POST['rcon_port'])){echo $_POST['rcon_port'];} ?>" placeholder="e.g. 6666">
											</div>										
										</div>
										<div class="col-md-6">	
											<div class="form-group">
												<label for=""><b class="text-muted d-block">Rcon password</b></label>
												<input type="text" autocomplete="off" class="form-control" name="rcon_password" value="<?php if(isset($_POST['rcon_password'])){echo $_POST['rcon_password'];} ?>" placeholder="e.g. your password">
											</div>											
										</div>										
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="d-block mt-2">
						<center><button type="submit" class="btn btn-primary mt-2 pr-4 pl-4" name="form1">Add my gameserver</button></center>
					</div>					
				</form>
			</div>
		</div>
	</div>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		const selectGame = document.getElementById('games_select');
		const cPort = document.getElementById('c_port_container');
		const qPort = document.getElementById('q_port_container');
		const adressIpDiv = document.querySelector('input[name="adress_ip"]').closest('.col-md-4');
		const adressIpLabel = adressIpDiv.querySelector('label b');
		const adressIpInput = adressIpDiv.querySelector('input[name="adress_ip"]');
		const rconInfo = document.getElementById('rcon_info_container');

		function togglePortFields() {
			if (selectGame.value === 'discord') {
				// Cacher les ports
				cPort.style.display = 'none';
				qPort.style.display = 'none';

				// Cacher la section Rcon
				if (rconInfo) rconInfo.style.display = 'none';

				// Changer texte label
				adressIpLabel.textContent = 'Invitation Code';

				// Changer placeholder
				adressIpInput.placeholder = 'e.g. https://discord.gg/abcdef';

				// Changer la classe col-md-4 en col-md-12
				adressIpDiv.classList.remove('col-md-4');
				adressIpDiv.classList.add('col-md-12');
			} else {
				// Afficher les ports
				cPort.style.display = 'block';
				qPort.style.display = 'block';

				// Afficher la section Rcon
				if (rconInfo) rconInfo.style.display = 'block';

				// Remettre le texte label
				adressIpLabel.textContent = 'Adress ip *';

				// Remettre placeholder
				adressIpInput.placeholder = 'e.g. 192.168.1.50';

				// Remettre la classe col-md-4
				adressIpDiv.classList.remove('col-md-12');
				adressIpDiv.classList.add('col-md-4');
			}
		}

		togglePortFields();
		selectGame.addEventListener('change', togglePortFields);
	});
	</script>	
<?php require_once __DIR__ . '/footer.php'; ?>