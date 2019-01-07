<!DOCTYPE html>
<html>
	<head>
	  <meta charset="utf-8">
	  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	  <title>Login Form</title>
	  <link rel="stylesheet" href="style.css">
	</head>
	<body>
		 <section class="container">
			<div class="login">
				<h1>Remote Apps ou desktop</h1>
				<form method="post" action="index.php">
					<p><input type="text" name="login" value="" placeholder="Username""></p>
					<p><input type="password" name="password" value="" placeholder="Password"></p>
					<p class="submit"><input type="submit" name="valider" value="Login"></p>
				</form>
			</div>
			<div class="login-help">
				<p></p>
			</div>
		 </section>
		<?php
		if(isset($_POST['valider'])){
			$server = "x.x.x.x"; // ip address for you server AD
			$port="389";	
			$racine="DC=mydomain, DC=com"; // identification domain  DC name DC extension
			$dn=$_POST['login'];
			$dnlong=$dn."@mydomain.com";
			$pw=$_POST['password'];
			$ds=ldap_connect($server);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			if ($ds)
				{
				$ldapbind=ldap_bind($ds,$dnlong,$pw);
				if ($ldapbind) {
					/*echo "Connexion Ok";
					echo"<br>";*/
					$sr=ldap_search($ds, $racine, "(samaccountname=$dn)", array("memberof", "primarygroupid"));
					$info=ldap_get_entries($ds, $sr);
					$mo = $info[0]['memberof'];
					$pi = $info[0]['primarygroupid'][0];
		
					array_shift($mo);

					$sr2=ldap_search($ds, $racine, "(objectcategory=group)", array("distinguishedname", "primarygrouptoken"));
					$info2=ldap_get_entries($ds, $sr2);

					array_shift($info2);

					foreach($info2 as $e) {
						if($e['primarygrouptoken'][0] == $pi) {
							$mo[] = $e['distinguishedname'][0];
							break;
						}
					}

					$gp="";
					for ($i=0; $i<count($mo); $i++) {
						$grclair=substr($mo[$i],3,strpos($mo[$i],',')-3);
						if ($grclair == "Groupe user your ad for remote app") {
							$gp="RA";
						}
						if ($grclair=="Groupe user or TSE") {
							$gp="BD";
						}
					}
          //mydomain.com = your server tse or rdw
					if ($gp=="RA"){
						$command= 'xfreerdp /v:mydomain.com /u:'.$dnlong.' /p:'.$pw.' /load-balance-info:"tsv://MS Terminal Services Plugin.1.Programmes_Remot" /f /bpp:32 /network:lan -menu-anims /cert-ignore';
						exec ('DISPLAY=:0.0; export DISPLAY; '.$command.' -display');
					}
					if ($gp=="BD"){
						$command= ' xfreerdp /v:mydomain.com /u:'.$dnlong.' /p:'.$pw.' /load-balance-info:"tsv://MS Terminal Services Plugin.1.Sessions_bureau" /f /bpp:32 /network:lan -menu-anims /cert-ignore';
						exec ('DISPLAY=:0.0; export DISPLAY; '.$command.' -display');
					};

				} else {
					/*echo "Connexion échouée";*/
					echo "<script type='text/javascript'>alert(\"Username Password Incorrect\");</script>";
				}
				ldap_close($ds);
				}
			else
				{
					/*echo "impossible de se connecter au serveur ldap";*/

				}
		}
		?>
	</body>
</html>
