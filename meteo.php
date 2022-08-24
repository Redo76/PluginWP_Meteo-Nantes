<?php
/*

Plugin Name: Meteo
Plugin URI: http://wordpress.org/plugins/meteo/
Description: Un plugin Wordpress qui affichera la météo courante de la ville de nantes dans l'interface administration de Wordpress.
Author: rhamadache
Version: 1.0
Author URI: http://mon-siteweb.com/

*/

function getAPI(){
    $city = get_option('ville');
    $unit = get_option('unit');
    $urlJson = "https://api.openweathermap.org/data/2.5/weather?q=" . $city . "&units=" . $unit . "&lang=fr&appid=92c3fd34ea87fe572aaad5a6f99029fb";

    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );

    try {
        $Json = file_get_contents($urlJson);
    }
    catch (Exception $e) {
        return false;
    }

    restore_error_handler();
    
    // Converts to an array 
    $myarray = json_decode($Json, true);
    return $myarray;

}

function displayMeteo(){
    $data = getAPI();

    if (!$data) {
        echo "<h4 style='color : red;'> La météo n'a pas été trouvée car la ville est incorrecte </h4>";
    }else {
        echo "<p> 
            <img src='http://openweathermap.org/img/w/" . $data["weather"][0]["icon"] . ".png'>" . 
            $data["name"] . " , " . $data["weather"][0]["description"] . " , " . $data["main"]["temp"] . "°" .
            "</p>";
    }

}

add_action( 'admin_notices', 'displayMeteo');

function meteo_setttings() {
    register_setting( 'meteo_settings', 'ville');
    register_setting( 'meteo_settings', 'unit');
    
}
add_action( 'admin_init', 'meteo_setttings' );

//Formulaire Settings
function settings_form() {
    ?>
    <h1>Météo</h1>

    <h3>Bienvenue dans l'assistant Météo</h3>

    <form method="post" action="options.php">
    <?php settings_fields( 'meteo_settings' ); ?>
    <?php do_settings_sections( 'meteo_settings' ); ?>
    <table class="form-table">

        <tr valign="top">
        <th scope="row">Ville par défaut :</th>
        <td><input type="text" name="ville" value="<?php echo esc_attr( get_option('ville') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Unité pour les températures</th>
        <td>
            <select name="unit">
                <option value="standard" <?= (get_option('unit') == "standard") ? 'selected' : '' ; ?>>Kelvin</option>
                <option value="metric" <?= (get_option('unit') == "metric") ? 'selected' : '' ; ?>>Celsius</option>
                <option value="imperial" <?= (get_option('unit') == "imperial") ? 'selected' : '' ; ?>>Fahrenheit</option>
            </select>
        </td>
        </tr>

    </table>
    
    <?php submit_button(); ?>

</form>
    <?php
}

function add_menu() {
    add_options_page( 'Meteo Setting', 'Meteo', 'manage_options', 'meteo', 'settings_form' );
}
add_action( 'admin_menu', 'add_menu');
