<?php 

// include DomPDF autoloader
require_once ( WP_PLUGIN_DIR . "/woocommerce-pdf-invoice/lib/dompdf/autoload.inc.php" );

// reference the Dompdf namespace
use Dompdf\Dompdf;

$server_configs = array(
  "PHP Version" => array(
    "required" => "5.0",
    "value"    => phpversion(),
    "result"   => version_compare(phpversion(), "5.0"),
  ),
  "DOMDocument extension" => array(
    "required" => true,
    "value"    => phpversion("DOM"),
    "result"   => class_exists("DOMDocument"),
  ),
  "PCRE" => array(
    "required" => true,
    "value"    => phpversion("pcre"),
    "result"   => function_exists("preg_match") && @preg_match("/./u", "a"),
    "failure"  => "PCRE is required with Unicode support (the \"u\" modifier)",
  ),
  "Zlib" => array(
    "required" => true,
    "value"    => phpversion("zlib"),
    "result"   => function_exists("gzcompress"),
    "fallback" => "Recommended to compress PDF documents",
  ),
  "MBString extension" => array(
    "required" => true,
    "value"    => phpversion("mbstring"),
    "result"   => function_exists("mb_send_mail"), // Should never be reimplemented in dompdf
    "fallback" => "Recommended, will use fallback functions",
  ),
  "GD" => array(
    "required" => true,
    "value"    => phpversion("gd"),
    "result"   => function_exists("imagecreate"),
    "fallback" => "Required if you have images in your documents",
  ),
  "APC" => array(
    "required" => "For better performances",
    "value"    => phpversion("apc"),
    "result"   => function_exists("apc_fetch"),
    "fallback" => "Recommended for better performances",
  ),
  "GMagick or IMagick" => array(
    "required" => "Better with transparent PNG images",
    "value"    => null,
    "result"   => extension_loaded("gmagick") || extension_loaded("imagick"),
    "fallback" => "Recommended for better performances",
  ),
);

if (($gm = extension_loaded("gmagick")) || ($im = extension_loaded("imagick"))) {
  $server_configs["GMagick or IMagick"]["value"] = ($im ? "IMagick ".phpversion("imagick") : "GMagick ".phpversion("gmagick"));
}

?>

<table class="pdfsetup form-table">
  <tr class="pdfheaderrow">
    <th></th>
    <th>Required</th>
    <th>Present</th>
  </tr>
  
  <?php 
  $row 		= 'even';
  $rowcount = 0;
  foreach( $server_configs as $label => $server_config ) { 
  
  	$rowcount++;
	$row = ($rowcount % 2 == 0 ? 'even' : 'odd');
  ?>
    <tr class="pdf-<?php echo $row; ?>">
      <th class="title"><?php echo $label; ?></th>
      <td><?php echo ($server_config["required"] === true ? "Yes" : $server_config["required"]); ?></td>
      <td class="<?php echo ($server_config["result"] ? "ok" : (isset($server_config["fallback"]) ? "warning" : "failed")); ?>">
        <?php
        echo $server_config["value"];
        if ($server_config["result"] && !$server_config["value"]) echo "Yes";
        if (!$server_config["result"]) {
          if (isset($server_config["fallback"])) {
            echo "<div>No. ".$server_config["fallback"]."</div>";
          }
          if (isset($server_config["failure"])) {
            echo "<div>".$server_config["failure"]."</div>";
          }
        }
        ?>
      </td>
    </tr>
  <?php } ?>
  
</table>

<h3 id="dompdf-config">Send test email with PDF attachment</h3>
<form method="post" action="" >
<table>
	<tr>
    	<th>Enter email address</th>
        <td><input type="email" name="pdfemailtest-emailaddress" /><?php wp_nonce_field('pdf_test_nonce_action','pdf_test_nonce'); ?></td>
        <td><input type="hidden" name="pdfemailtest" value="1" /><input type="submit" value="Send test email with PDF Attachment" /></td>
	</tr>
</table>
</form>