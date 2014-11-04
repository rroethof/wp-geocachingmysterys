<?php
/* Plugin Name: Mystery Oplossingen
Plugin URI: http://www.geomaatjes.nl/
Description: Mystery Oplossingen
Version: 1.1
Author: Ronny Roethof
Author URI: http://www.familieroethof.nl/
License: GPLv2 or later
*/

global $mystery_db_version;
$mystery_db_version = '1.1';

// Activate plugin
function Mystery_install() {
	global $wpdb;
	global $mystery_db_version;
	
	$table_name = $wpdb->prefix . 'mysterys';
	
	/*
	 * We'll set the default character set and collation for this table.
	 * If we don't do this, some characters could end up being converted 
	 * to just ?'s when saved in our table.
	 */
	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
	  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
	  $charset_collate .= " COLLATE {$wpdb->collate}";
	}

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

// Add the admin menu
add_action( 'admin_menu', 'addMysteryMenu' );

// The function for the admin menu
function addMysteryMenu(){
    add_menu_page('Mystery lijstje', 'Mystery\'s', 'manage_options', 'Mystery_settings_page', 'Mystery_settings_page', 'dashicons-cart', 3);
    add_submenu_page('Mystery_settings_page', 'Mystery toevoegen', 'Mystery toevoegen', 'manage_options', 'Mystery_items_add', 'Mystery_items_add');
}

// INDEX PAGINA
function Mystery_settings_page() {
    echo "<div class='wrap'>";
    echo "<h2>Mystery Overzicht </h2>";

    echo "<table class='wp-list-table widefat fixed footable'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th id='id' class='manage-column column-role' style='width: 100%;' scope='col'>#</th>";
    echo "<th id='gccode' class='manage-column column-role' style='width: 100%;' scope='col'>GCCode</th>";
    echo "<th id='plaats' class='manage-column column-role' style='width: 100%;' scope='col'>Plaats</th>";
    echo "<th id='naam' class='manage-column column-role' style='width: 100%;' scope='col' >Naam</th>";
    echo "<th id='oplossing' class='manage-column column-role' style='width: 100%;' scope='col' >Oplossing</th>";
    echo "<th id='actie1' class='manage-column column-role' style='width: 100%;' scope='col' >Actie</th>";
    echo "</tr>";

    $colorid = 0;
    $bedrag = 0;
    
    global $wpdb;
	$table_name = $wpdb->prefix . 'mysterys';

	$allposts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name ORDER BY naam ASC"));
	foreach ($allposts as $mysteryitem) { 
		if ($colorid % 2 != 0) { # An odd row
		    $rowColor = "";
		} else { # An even row
		    $rowColor = "alternate";
		}
		echo "<tr class='".$rowColor."'>";
        echo "<td>".$mysteryitem->id."</td>";
        echo "<td><a href='".$mysteryitem->url."' target='_blank'>".$mysteryitem->gccode."</a></td>";
        echo "<td>".$mysteryitem->plaats."</td>";
        echo "<td><a href='".$mysteryitem->url."' target='_blank'>".$mysteryitem->naam."</a></td>";

        if($mysteryitem->noord != '') {
            echo "<td>N ".$mysteryitem->noord." &nbsp; E ".$mysteryitem->oost."</td>";
        } else {
            echo "<td>".$mysteryitem->code."</td>";
        }

        echo "<td width=300>";
		if($mysteryitem->noord != '' || $mysteryitem->oost != '' || $mysteryitem->code != '' ) {
		    echo "<a href='http://geomaatjes.nl/wp-admin/admin.php?page=Mystery_items_add&id=".$mysteryitem->id."' title='bewerken'><div class='dashicons dashicons-clipboard'></div></a>";
		} else {
		    echo "<a href='http://geomaatjes.nl/wp-admin/admin.php?page=Mystery_items_add&id=".$mysteryitem->id."' title='bewerken'><div class='dashicons dashicons-clipboard'></div></a>";
			echo "&nbsp;&nbsp;&nbsp;";
		    echo "<a href='http://geomaatjes.nl/wp-admin/admin.php?page=Mystery_items_add&id=".$mysteryitem->id."' title='oplossen'><div class='dashicons dashicons-admin-site'></div></a>";
		}
        echo "</td>";
        echo "</tr>";
	$colorid++;
    }
    echo "</table>";
} 

// TOEVOEGEN PAGINA
function Mystery_items_add() {
    ?>
    <div class="wrap">

    <?php
    if (isset($_POST["voegitemtoe"])) {
        // Do the saving
		global $wpdb;
		$table_name = $wpdb->prefix . 'mysterys';
		$wpdb->show_errors();
		if(isset($_POST['id'])) {

			// Update
			$wpdb->query("UPDATE $table_name SET 
    		    gccode = '".$_POST['gccode']."',
    		    plaats = '".$_POST['plaats']."',
    	    	naam = '".$_POST['naam']."',
	    	    notitie = '".$_POST['notitie']."',
    		    noord = '".$_POST['noord']."',
    		    oost =  '".$_POST['oost']."',
    	    	url =  '".$_POST['url']."'
				WHERE id = '".$_POST['id']."'
			");
			echo "<h2>Mystery Oplossingen: Item Oplossen</h2>";
			echo "GC Code: ".$_POST['gccode']."<br>";
			echo "Naam: ".$_POST['naam']."<br>";
			echo "Oplossen gelukt";
			echo "<br>";
			echo "<a href='?page=Mystery_settings_page'>Overzicht</a>";

		} else {
			$wpdb->insert($table_name, array(
    		    "gccode" => $_POST['gccode'],
    		    "plaats" => $_POST['plaats'],
    	    	"naam" => $_POST['naam'],
	    	    "notitie" => nl2br($_POST['notitie']),
    		    "noord" => $_POST['noord'],
    		    "oost" => $_POST['oost'],
    	    	"url" => $_POST['url']
			));
		
			echo "<h2>Mystery Oplossingen: Item Toevoegen</h2>";
			echo "GC Code: ".$_POST['gccode']."<br>";
			echo "Naam: ".$_POST['naam']."<br>";
			echo "Toevoegen gelukt";
			echo "<br>";
			echo "<a href='?page=Mystery_items_add'>Nieuwe item toevoegen</a>";

		}
    } else {
		?>

	    <style>
		/* Elegant Aero */
		.elegant-aero {
		    margin-left:auto;
		    margin-right:auto;

		    max-width: 500px;
		    background: #D2E9FF;
		    padding: 20px 20px 20px 20px;
		    font: 12px Arial, Helvetica, sans-serif;
		    color: #666;
		}
		.elegant-aero h1 {
		    font: 24px "Trebuchet MS", Arial, Helvetica, sans-serif;
		    padding: 10px 10px 10px 20px;
		    display: block;
		    background: #C0E1FF;
		    border-bottom: 1px solid #B8DDFF;
		    margin: -20px -20px 15px;
		}
		.elegant-aero h1>span {
		    display: block;
		    font-size: 11px;
		}

		.elegant-aero label>span {
		    float: left;
		    margin-top: 10px;
		    color: #5E5E5E;
		}
		.elegant-aero label {
		    display: block;
		    margin: 0px 0px 5px;
		}
		.elegant-aero label>span {
		    float: left;
		    width: 20%;
		    text-align: right;
		    padding-right: 15px;
		    margin-top: 10px;
		    font-weight: bold;
		}
		.elegant-aero input[type="text"], .elegant-aero input[type="email"], .elegant-aero textarea, .elegant-aero select {
		    color: #888;
		    width: 70%;
		    padding: 0px 0px 0px 5px;
		    border: 1px solid #C5E2FF;
		    background: #FBFBFB;
		    outline: 0;
		    -webkit-box-shadow:inset 0px 1px 6px #ECF3F5;
		    box-shadow: inset 0px 1px 6px #ECF3F5;
		    font: 200 12px/25px Arial, Helvetica, sans-serif;
		    height: 30px;
		    line-height:15px;
		    margin: 2px 6px 16px 0px;
		}
		.elegant-aero textarea{
		    height:100px;
		    padding: 5px 0px 0px 5px;
		    width: 70%;
		}
		.elegant-aero select {
		    background: #fbfbfb url('down-arrow.png') no-repeat right;
		    background: #fbfbfb url('down-arrow.png') no-repeat right;
		    appearance:none;
		    -webkit-appearance:none; 
		   -moz-appearance: none;
		    text-indent: 0.01px;
		    text-overflow: '';
		    width: 70%;
		}
		.elegant-aero .button{
		    padding: 10px 30px 10px 30px;
		    background: #66C1E4;
		    border: none;
		    color: #FFF;
		    box-shadow: 1px 1px 1px #4C6E91;
		    -webkit-box-shadow: 1px 1px 1px #4C6E91;
		    -moz-box-shadow: 1px 1px 1px #4C6E91;
		    text-shadow: 1px 1px 1px #5079A3;
		}
		.elegant-aero .button:hover{
		    background: #3EB1DD;
		}
 	    </style>
    
	    <div class="formLayout">
        
        <form name="" method="post" action="" class="elegant-aero">
	        <?php
			if(isset($_GET['id'])) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'mysterys';
            	$mysterycache = $wpdb->get_row("SELECT * FROM $table_name WHERE id = '".$_GET['id']."'");
				?>
		    <h1>Mystery Oplossingen: Mystery Oplossen
	    	    <span>Please fill all the texts in the fields.</span>
	    	</h1>
            <input id="id" type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
            	<?php
			} else {
				?>
		    <h1>Mystery Oplossingen: Mystery Toevoegen 
	    	    <span>Please fill all the texts in the fields.</span>
	    	</h1>
            	<?php
			}
			?>
		    <label>
            	<span>GC Code :</span>
		        <input id="gccode" type="text" name="gccode" <?php if(!isset($_GET['id'])) { echo "placeholder=\"GC Code\" />"; } else { echo "value=\"$mysterycache->gccode\" />"; } ?>
		    </label>
    
		    <label>
        		<span>Plaats :</span>
		        <input id="plaats" type="text" name="plaats" <?php if(!isset($_GET['id'])) { echo "placeholder=\"Plaats\" />"; } else { echo "value=\"$mysterycache->plaats\" />"; } ?>
		    </label>

		    <label>
        		<span>Naam :</span>
		        <input id="naam" type="text" name="naam" <?php if(!isset($_GET['id'])) { echo "placeholder=\"Naam\" />"; } else { echo "value=\"$mysterycache->naam\" />"; } ?>
		    </label>

		    <label>
        		<span>URL :</span>
		        <input id="url" type="text" name="url" <?php if(!isset($_GET['id'])) { echo "placeholder=\"URL\" />"; } else { echo "value=\"$mysterycache->url\" />"; } ?>
		    </label>

		    <label>
        		<span>Coordinaten :</span>
		        <input id="noord" type="text" name="noord" <?php if(!isset($_GET['id'])) { echo "placeholder=\"Noord\" />"; } else { echo "value=\"$mysterycache->noord\" />"; } ?>
        		<span></span>
		        <input id="oost" type="text" name="oost" <?php if(!isset($_GET['id'])) { echo "placeholder=\"Oost\" />"; } else { echo "value=\"$mysterycache->oost\" />"; } ?>
		    </label>
    
		    <label>
        		<span>Notitie :</span>
		        <textarea id="notitie" name="notitie" <?php if(!isset($_GET['id'])) { echo "placeholder=\"Notitie\" />"; } else { echo "/>$mysterycache->notitie"; } ?></textarea>
		    </label> 

		     <label>
			    <input type="hidden" name="voegitemtoe" value="Y" />
        		<span>&nbsp;</span> 
                <?php if(!isset($_GET['id'])) { ?>
			        <input type="submit" class="button" name="Submit" value="Voeg Toe" /> 
                <?php } else { ?>
			        <input type="submit" class="button" name="Submit" value="Los Op" /> 
                <?php } ?>
		    </label>    
		</form>
    	</div>
    <?php
   	}
    ?>
    </div>
<?php
} 
?>
