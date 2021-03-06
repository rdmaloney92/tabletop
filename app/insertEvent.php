<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);

var_dump($_POST);

function create_slug($string){
   $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return $slug;
}

// Define the Database connection
const SERVER_NAME = "160.153.93.162";
const USER_NAME = "tabletop_fb";
const PASSWORD = "Tabletop1!";
const DB_NAME = "tabletop_wp1";

// Define the Keys used in the array
const KEY_DESCRIPTION = "description";
const KEY_TITLE = "postTitle";
const KEY_POST_ID = "postId";
const KEY_EXPIRATION_DATE = "expirationDate";
const KEY_BASE_PRICE = "basePrice";
const KEY_DYNAMIC_PRICE = "dynamicPrice";
const KEY_MIN_PURCHASES = "minPurchases";
const KEY_MAX_PURCHASES = "maxPurchases";
const KEY_MAX_PURCHASES_PER_USER = "maxPurchasesPerUser";
const KEY_VALUE = "value";
const KEY_AMOUNT_SAVED = "amountSaved";
const KEY_HIGHLIGHTS = "highlights";
const KEY_FINE_PRINT = "finePrint";
const KEY_VOUCHER_EXPIRATION_DATE = "voucherExpirationDate";
const KEY_VOUCHER_HOW_TO_USE = "voucherHowToUse";
const KEY_VOUCHER_MAP = "voucherMap";
const KEY_VOUCHER_SERIAL_NUMBER = "voucherSerialNumber";
const KEY_MERCHANT_ID = "merchantId";
const KEY_VOUCHER_LOCATIONS = "voucherLocations";
const KEY_WP_ATTACHED_FILE = "wpAttachedFile";
const KEY_WP_ATTACHMENT_METADATA = "wpAttachmentMetaData";
const KEY_THUMBNAIL_ID = "thumbnailId";
const KEY_NUMBER_OF_PURCHASES = "numberOfPurchases";
const KEY_EDIT_LOCK = "editLock";
const KEY_EDIT_LAST = "editLast";
const KEY_REDIRECT_URL = "redirectUrl";
const KEY_TAXABLE = "taxable";
const KEY_TAX = "tax";
const KEY_SHIPPING = "shipping";
const KEY_SHIPPING_MODE = "shippingMode";
const KEY_SHIPPING_DYN_PRICE = "shippingDynPrice";
const KEY_RSS_EXCERPT = "rssExcerpt";
const KEY_VOUCHER_ID_PREFIX = "voucherIdPrefix";
const KEY_VOUCHER_LOGO = "voucherLogo";
const KEY_CAPTURE_BEFORE_EXPIRATION = "captureBeforeExpiration";
const KEY_PREVIEW_PRIVATE_KEY = "previewPrivateKey";
const KEY_FEATURED_CONTENT = "featuredContent";

// Define the Standard fields
$authorId = 2;
$empty = "";
$zero = 0;
$one = 1;
$hundred = 100;
$description = "&nbsp;";

$merchantId = 214;

$id = 0;
$thumbId = 0;

$hash = md5(uniqid(rand(), true));

$taxRate = "standard-rate";
$shippingMode = "flat-rate-5";
$shippingDynPrice = "a:0:{}";

$highlight = "Check out this deal!";
$finePrint = "Read the fine print...";

date_default_timezone_set("America/Phoenix");
$today = (new DateTime("NOW"))->format("Y-m-d H:i:s");
$expiration = (new DateTime("NOW"))->format("Y-m-d H:i:s");
date_default_timezone_set("Europe/London");
$todayGmt = (new DateTime("NOW"))->format("Y-m-d H:i:s");

$array = array();
$array['success'] = false;

// Access the Post data
$data = $_POST;

if (empty($data)) {
	echo ("ERROR: DATA NULL\n");
	die();
}

// Store the Request to a file
$file = file_get_contents('data.json');
unset($file); // Prevent memory leaks for large json.
file_put_contents('data.json',json_encode($data));

// Create connection
$conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
	echo json_encode($array);
    die();
}

mysqli_report(MYSQLI_REPORT_ALL);

// Initialize the Event Queries
$selectIdQuery = "SELECT MAX(ID) FROM wp_posts";
$deletePostQuery = "DELETE FROM wp_posts WHERE ID = ?";
$insertPostQuery = "INSERT INTO wp_posts(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
          post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged,
          post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order,
          post_type, post_mime_type, comment_count) VALUES
          (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$deleteMetadataQuery = "DELETE FROM wp_postmeta WHERE post_id = ?";
$insertMetadataQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
					(?, '_expiration_date', ?),
					(?, '_base_price', ?),
					(?, '_dynamic_price', ?),
					(?, '_min_purchases', ?),
					(?, '_max_purchases', ?),
					(?, '_max_purchases_per_user', ?),
					(?, '_value', ?),
					(?, '_amount_saved', ?),
					(?, '_highlights', ?),
					(?, '_fine_print', ?),
					(?, '_voucher_expiration_date', ?),
					(?, '_voucher_how_to_use', ?),
					(?, '_voucher_map', ?),
					(?, '_voucher_serial_number', ?),
					(?, '_merchant_id', ?),
					(?, '_voucher_locations', ?),
					(?, '_thumbnail_id', ?),
					(?, '_number_of_purchases', ?),
					(?, '_edit_lock', ?),
					(?, '_edit_last', ?),
					(?, '_redirect_url', ?),
					(?, '_taxable', ?),
					(?, '_tax', ?),
					(?, '_shipping', ?),
					(?, '_shipping_mode', ?),
					(?, '_shipping_dyn_price', ?),
					(?, '_rss_excerpt', ?),
					(?, '_voucher_id_prefix', ?),
					(?, '_voucher_logo', ?),
					(?, '_capture_before_expiration', ?),
					(?, '_preview_private_key', ?),
					(?, '_featured_content', ?)";

$thumbnailQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
					(?, '_wp_attached_file', ?),
					(?, '_wp_attachment_metadata', ?)";

if ($result = $conn->query($selectIdQuery)) {
  $row = $result->fetch_array(MYSQLI_NUM);

  if (is_numeric($row[0])) {
    $id = $row[0] + 1;
    $thumbId = $id + 2;
  }
} else {
  echo "ERROR - Could not retrieve ID from the wp_posts table\n";
  die();
}

// Delete current post meta data
$stmt = $conn->prepare($deleteMetadataQuery);
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete current post data
$stmt = $conn->prepare($deletePostQuery);
$stmt->bind_param("i", $id);
$stmt->execute();

// Insert new post data
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi",
  $authorId,
  $today,
  $todayGmt,
  ($data[KEY_DESCRIPTION] != null && is_string($data[KEY_DESCRIPTION]) && strlen($data[KEY_DESCRIPTION]) > 0) ? $data[KEY_DESCRIPTION] : $description,
  $data[KEY_TITLE],
  $empty,
  $a = "publish",
  $b = "open",
  $c = "open",
  $empty,
  create_slug($data[KEY_TITLE]),
  $empty,
  $empty,
  $today,
  $todayGmt,
  $empty,
  $zero,
  $d = ("http://www.tabletopdine.com/dev/?post_type=wg_merchant&#038;p=" . $id),
  $zero,
  $e = "wg_deal",
  $empty,
  $zero
);
$stmt->execute();

// Insert new post data for revision row
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi",
  $authorId,
  $today,
  $todayGmt,
  $empty,
  $data[KEY_TITLE],
  $empty,
  $a = "inherit",
  $b = "open",
  $c = "open",
  $empty,
  $d = ($id . "-revision-v1"),
  $empty,
  $empty,
  $today,
  $todayGmt,
  $empty,
  $id,
  $e = ("http://www.tabletopdine.com/dev/" . $id . "-revision-v-1"),
  $zero,
  $f = "revision",
  $empty,
  $zero
);
$stmt->execute();

// Insert Image data for event
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi",
  $authorId,
  $today,
  $todayGmt,
  $hash,
  $hash,
  $empty,
  $a = "inherit",
  $b = "open",
  $c = "open",
  $empty,
  $hash,
  $empty,
  $empty,
  $today,
  $todayGmt,
  $empty,
  $id,
  $d = ("http://stuffpoint.com/food/image/235785-food-fast-food-combo.png"),
  $zero,
  $e = "attachment",
  $f = "image/png",
  $zero
);
$stmt->execute();

// // Insert new post meta data
$stmt = $conn->prepare($insertMetadataQuery);
$stmt->bind_param("isididiiiiiiididisisisisisisiiisiiiiisisisisisisisisisisisisisis",
	$id, ($data[KEY_EXPIRATION_DATE] != null && is_string($data[KEY_EXPIRATION_DATE]) && !empty($data[KEY_EXPIRATION_DATE])) ? $data[KEY_EXPIRATION_DATE] : $expiration,
	$id, ($data[KEY_BASE_PRICE] != null && is_numeric($data[KEY_BASE_PRICE]) && $data[KEY_BASE_PRICE] > 0) ? $data[KEY_BASE_PRICE] : $zero,
	$id, ($data[KEY_DYNAMIC_PRICE] != null && is_numeric($data[KEY_DYNAMIC_PRICE]) && $data[KEY_DYNAMIC_PRICE] > 0) ? $data[KEY_DYNAMIC_PRICE] : $zero,
	$id, ($data[KEY_MIN_PURCHASES] != null && is_numeric($data[KEY_MIN_PURCHASES]) && $data[KEY_MIN_PURCHASES] > 0) ? $data[KEY_MIN_PURCHASES] : $one,
	$id, ($data[KEY_MAX_PURCHASES] != null && is_numeric($data[KEY_MAX_PURCHASES]) && $data[KEY_MAX_PURCHASES] > 0) ? $data[KEY_MAX_PURCHASES] : $hundred,
	$id, ($data[KEY_MAX_PURCHASES_PER_USER] != null && is_numeric($data[KEY_MAX_PURCHASES_PER_USER]) && $data[KEY_MAX_PURCHASES_PER_USER] > 0) ? $data[KEY_MAX_PURCHASES_PER_USER] : $one,
	$id, ($data[KEY_VALUE] != null && is_numeric($data[KEY_VALUE]) && $data[KEY_VALUE] > 0) ? $data[KEY_VALUE] : $zero,
	$id, ($data[KEY_AMOUNT_SAVED] != null && is_numeric($data[KEY_VALUE]) && $data[KEY_AMOUNT_SAVED] > 0) ? $data[KEY_VALUE] : $zero,
	$id, ($data[KEY_HIGHLIGHTS] != null && is_string($data[KEY_HIGHLIGHTS]) && !empty($data[KEY_HIGHLIGHTS])) ? $data[KEY_HIGHLIGHTS] : $highlight,
	$id, ($data[KEY_FINE_PRINT] != null && is_string($data[KEY_FINE_PRINT]) && !empty($data[KEY_FINE_PRINT])) ? $data[KEY_FINE_PRINT] : $finePrint,
	$id, ($data[KEY_VOUCHER_EXPIRATION_DATE] != null && is_string($data[KEY_VOUCHER_EXPIRATION_DATE]) && !empty($data[KEY_VOUCHER_EXPIRATION_DATE])) ? $data[KEY_VOUCHER_EXPIRATION_DATE] : $expiration,
	$id, $data[KEY_VOUCHER_HOW_TO_USE],
	$id, $data[KEY_VOUCHER_MAP],
	$id, ($data[KEY_VOUCHER_SERIAL_NUMBER] != null && is_string($data[KEY_VOUCHER_SERIAL_NUMBER]) && !empty($data[KEY_VOUCHER_SERIAL_NUMBER])) ? $data[KEY_VOUCHER_SERIAL_NUMBER] : $hash,
	$id, ($data[KEY_MERCHANT_ID] != null && is_string($data[KEY_MERCHANT_ID]) && !empty($data[KEY_MERCHANT_ID])) ? $data[KEY_MERCHANT_ID] : $merchantId,
	$id, $data[KEY_VOUCHER_LOCATIONS],
	$id, $thumbId,
	$id, $zero,
	$id, $data[KEY_EDIT_LOCK],
	$id, $data[KEY_EDIT_LAST],
	$id, $data[KEY_REDIRECT_URL],
	$id, $data[KEY_TAXABLE],
	$id, $taxRate,
	$id, $data[KEY_SHIPPING],
	$id, $shippingMode,
	$id, $shippingDynPrice,
	$id, $data[KEY_RSS_EXCERPT],
	$id, $data[KEY_VOUCHER_ID_PREFIX],
	$id, $data[KEY_VOUCHER_LOGO],
	$id, $data[KEY_CAPTURE_BEFORE_EXPIRATION],
	$id, $data[KEY_PREVIEW_PRIVATE_KEY],
	$id, $data[KEY_FEATURED_CONTENT]
);
$stmt->execute();

// $stmt = $conn->prepare($thumbnailQuery);
// $stmt->bind_param("isis",
// 	$id, $data[KEY_WP_ATTACHED_FILE],
// 	$id, $data[KEY_WP_ATTACHMENT_METADATA]
// );
// $stmt->execute();

$array['success'] = true;
echo json_encode($array);

$conn->close();

// Release memory
unset($data);
unset($array);
unset($stmt);
unset($deleteQuery);
unset($insertQuery);
unset($thumbnailQuery);
unset($conn);

?>
