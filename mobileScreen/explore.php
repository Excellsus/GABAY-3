<?php
// Enable error reporting for debugging (remove or adjust for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Go up one directory to include connect_db.php from the parent folder
include __DIR__ . '/../connect_db.php'; // Include database connection

$offices = []; // Initialize as empty array
$error_message_php = null; // Variable for PHP errors

$highlight_office_id = null; // This will store the office ID from the URL if provided
if (isset($_GET['office_id']) && is_numeric($_GET['office_id'])) {
    $highlight_office_id = (int)$_GET['office_id'];
}

// --- Log QR Scan if office_id is present in URL ---
if ($highlight_office_id !== null && isset($connect) && $connect) {
    try {
        // First, find the id from qrcode_info table that corresponds to this office_id
        // This id will be used as qr_code_id in qr_scan_logs
        $stmt_qr_info = $connect->prepare("SELECT id FROM qrcode_info WHERE office_id = :office_id LIMIT 1");
        $stmt_qr_info->bindParam(':office_id', $highlight_office_id, PDO::PARAM_INT);
        $stmt_qr_info->execute();
        $qr_info_record = $stmt_qr_info->fetch(PDO::FETCH_ASSOC);

        if ($qr_info_record && isset($qr_info_record['id'])) {
            $qr_code_info_id = $qr_info_record['id'];

            // Now, insert the scan log
            $stmt_log = $connect->prepare("INSERT INTO qr_scan_logs (office_id, qr_code_id, check_in_time) VALUES (:office_id, :qr_code_id, NOW())");
            $stmt_log->bindParam(':office_id', $highlight_office_id, PDO::PARAM_INT);
            $stmt_log->bindParam(':qr_code_id', $qr_code_info_id, PDO::PARAM_INT);
            $stmt_log->execute();
            error_log("QR Scan logged for office_id: $highlight_office_id, qr_code_info_id: $qr_code_info_id");
        } else {
            error_log("QR Scan attempt for office_id: $highlight_office_id, but no corresponding qrcode_info record found.");
        }
    } catch (PDOException $e) {
        error_log("Error logging QR scan: " . $e->getMessage());
    }
}
// --- End Log QR Scan ---


try {
    // Check if $connect is a valid PDO object
    if (!isset($connect) || !$connect) {
        throw new Exception("Database connection object (\$connect) is not valid. Check connect_db.php.");
    }

    // Fetch all office data
    $stmt = $connect->query("SELECT id, name, details, contact, location FROM offices");

    // Check if query execution was successful
    if ($stmt === false) {
        // Query failed, get error info
        $errorInfo = $connect->errorInfo();
        throw new PDOException("Query failed: " . ($errorInfo[2] ?? 'Unknown error - Check table/column names and permissions.'));
    }

    // Fetch the data
    $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { // Catches PDOException and general Exception
    $error_message_php = "Error fetching office data: " . $e->getMessage();
    error_log("Error in explore.php (PHP part): " . $e->getMessage()); // Log error to PHP error log
}

?>
  
  <!DOCTYPE html>
  <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Visitor Navigation</title>
    <link rel="stylesheet" href="explore.css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
     <link
       rel="stylesheet"
       href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"

      />
    <body>
      <header class="header">
        <div class="header-content">
            <h2 class="section-title">Explore</h2>
            <p class="section-subtitle">Interactive Floor Plan</p>
        </div>
        <!-- Placeholder for potential future actions -->
        <div class="header-actions"></div>
      </header>
    </head>
      <!-- Main content area -->
    <!-- Removed complex height style, will be handled by CSS -->
    <main class="content">       <!-- Tooltip Element -->
      <div id="floorplan-tooltip" class="absolute bg-black text-white text-xs px-2 py-1 rounded shadow-lg pointer-events-none hidden z-50"></div>
        <?php if ($error_message_php): ?>
            <p class="error-message" style="text-align: center; padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin: 10px;"><?php echo htmlspecialchars($error_message_php); ?></p>
        <?php endif; ?>

        <!-- SVG Container -->
        <!-- Adjusted container for better fit on mobile -->
        <div class="flex-grow bg-white rounded-lg card-shadow overflow-hidden flex relative h-full w-full"> <!-- Use full height/width -->
          <div class="floor-plan-container flex-grow relative w-full h-full"> <!-- Ensure inner div also fills space -->
          <!-- Removed invalid XML declaration -->
          <!-- Created with Inkscape (http://www.inkscape.org/) -->
          
          <svg
             width="1917.8289"
             height="629.6413"
             viewBox="0 0 1917.8289 629.6413"
             version="1.1"
             id="svg1"
             xml:space="preserve"
             xmlns:xlink="http://www.w3.org/1999/xlink"
             xmlns="http://www.w3.org/2000/svg"
             xmlns:svg="http://www.w3.org/2000/svg"><defs
               id="defs1"><linearGradient
                 id="swatch39"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop39" /></linearGradient><linearGradient
                 id="swatch38"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop38" /></linearGradient><linearGradient
                 id="swatch36"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop36" /></linearGradient><linearGradient
                 id="swatch34"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop34" /></linearGradient><linearGradient
                 id="swatch12"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop12" /></linearGradient><linearGradient
                 id="swatch86"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop86" /></linearGradient><linearGradient
                 id="swatch85"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop85" /></linearGradient><linearGradient
                 id="swatch84"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop84" /></linearGradient><linearGradient
                 id="swatch83"><stop
                   style="stop-color:#5b1025;stop-opacity:1;"
                   offset="0"
                   id="stop83" /></linearGradient><linearGradient
                 id="swatch82"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop82" /></linearGradient><linearGradient
                 id="swatch79"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop79" /></linearGradient><linearGradient
                 id="swatch78"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop78" /></linearGradient><linearGradient
                 id="swatch77"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop77" /></linearGradient><linearGradient
                 id="swatch76"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop76" /></linearGradient><linearGradient
                 id="swatch75"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop75" /></linearGradient><linearGradient
                 id="swatch74"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop74" /></linearGradient><linearGradient
                 id="swatch73"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop73" /></linearGradient><linearGradient
                 id="swatch72"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop72" /></linearGradient><linearGradient
                 id="swatch71"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop71" /></linearGradient><linearGradient
                 id="swatch70"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop70" /></linearGradient><linearGradient
                 id="swatch68"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop69" /></linearGradient><linearGradient
                 id="swatch65"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop65" /></linearGradient><linearGradient
                 id="swatch64"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop64" /></linearGradient><linearGradient
                 id="swatch37"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop37" /></linearGradient><linearGradient
                 id="swatch35"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop35" /></linearGradient><linearGradient
                 id="swatch33"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop33" /></linearGradient><linearGradient
                 id="swatch32"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop32" /></linearGradient><linearGradient
                 id="swatch31"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop31" /></linearGradient><linearGradient
                 id="swatch30"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop30" /></linearGradient><linearGradient
                 id="swatch29"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop29" /></linearGradient><linearGradient
                 id="swatch28"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop28" /></linearGradient><linearGradient
                 id="swatch27"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop27" /></linearGradient><linearGradient
                 id="swatch26"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop26" /></linearGradient><linearGradient
                 id="swatch25"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop25" /></linearGradient><linearGradient
                 id="swatch24"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop24" /></linearGradient><linearGradient
                 id="swatch23"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop23" /></linearGradient><linearGradient
                 id="swatch22"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop22" /></linearGradient><linearGradient
                 id="swatch21"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop21" /></linearGradient><linearGradient
                 id="swatch20"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop20" /></linearGradient><linearGradient
                 id="swatch19"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop19" /></linearGradient><linearGradient
                 id="swatch18"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop18" /></linearGradient><linearGradient
                 id="swatch17"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop17" /></linearGradient><linearGradient
                 id="swatch16"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop16" /></linearGradient><linearGradient
                 id="swatch15"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop15" /></linearGradient><linearGradient
                 id="swatch14"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop14" /></linearGradient><linearGradient
                 id="swatch13"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop13" /></linearGradient><linearGradient
                 id="swatch11"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop11" /></linearGradient><linearGradient
                 id="swatch10"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop10" /></linearGradient><linearGradient
                 id="swatch9"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop9" /></linearGradient><linearGradient
                 id="swatch8"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop8" /></linearGradient><linearGradient
                 id="swatch7"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop7" /></linearGradient><linearGradient
                 id="swatch6"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop6" /></linearGradient><linearGradient
                 id="swatch5"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop5" /></linearGradient><linearGradient
                 id="swatch4"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop4" /></linearGradient><linearGradient
                 id="swatch3"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop3" /></linearGradient><linearGradient
                 id="swatch2"><stop
                   style="stop-color:#000000;stop-opacity:1;"
                   offset="0"
                   id="stop2" /></linearGradient><linearGradient
                 id="swatch1"><stop
                   style="stop-color:#ffffff;stop-opacity:1;"
                   offset="0"
                   id="stop1" /></linearGradient><linearGradient
                 xlink:href="#swatch65"
                 id="linearGradient57"
                 gradientUnits="userSpaceOnUse"
                 x1="70.251472"
                 y1="313.12021"
                 x2="109.73022"
                 y2="313.12021" /><linearGradient
                 xlink:href="#swatch68"
                 id="linearGradient58"
                 gradientUnits="userSpaceOnUse"
                 x1="46.956913"
                 y1="261.43719"
                 x2="84.081917"
                 y2="261.43719" /><linearGradient
                 xlink:href="#swatch70"
                 id="linearGradient59"
                 gradientUnits="userSpaceOnUse"
                 x1="87.969109"
                 y1="459.78064"
                 x2="124.97106"
                 y2="459.78064" /><linearGradient
                 xlink:href="#swatch71"
                 id="linearGradient60"
                 gradientUnits="userSpaceOnUse"
                 x1="206.05595"
                 y1="454.56815"
                 x2="243.38017"
                 y2="454.56815" /><linearGradient
                 xlink:href="#swatch72"
                 id="linearGradient61"
                 gradientUnits="userSpaceOnUse"
                 x1="203.4043"
                 y1="374.13474"
                 x2="240.45898"
                 y2="374.13474" /><linearGradient
                 xlink:href="#swatch73"
                 id="linearGradient62"
                 gradientUnits="userSpaceOnUse"
                 x1="183.42853"
                 y1="219.01958"
                 x2="220.68243"
                 y2="219.01958" /><linearGradient
                 xlink:href="#swatch74"
                 id="linearGradient63"
                 gradientUnits="userSpaceOnUse"
                 x1="419.83917"
                 y1="329.95282"
                 x2="455.90939"
                 y2="329.95282" /><linearGradient
                 xlink:href="#swatch75"
                 id="linearGradient64"
                 gradientUnits="userSpaceOnUse"
                 x1="561.02356"
                 y1="306.69202"
                 x2="598.21301"
                 y2="306.69202" /><linearGradient
                 xlink:href="#swatch78"
                 id="linearGradient67"
                 gradientUnits="userSpaceOnUse"
                 x1="708.75085"
                 y1="202.30832"
                 x2="746.86414"
                 y2="202.30832" /><linearGradient
                 xlink:href="#swatch79"
                 id="linearGradient68"
                 gradientUnits="userSpaceOnUse"
                 x1="837.82953"
                 y1="151.09354"
                 x2="878.10645"
                 y2="151.09354" /><linearGradient
                 xlink:href="#swatch85"
                 id="linearGradient76"
                 gradientUnits="userSpaceOnUse"
                 x1="1139.6543"
                 y1="299.3493"
                 x2="1184.2852"
                 y2="299.3493" /><linearGradient
                 xlink:href="#swatch82"
                 id="linearGradient80"
                 gradientUnits="userSpaceOnUse"
                 x1="886.99445"
                 y1="315.94324"
                 x2="932.25031"
                 y2="315.94324" /><linearGradient
                 xlink:href="#swatch84"
                 id="linearGradient81"
                 gradientUnits="userSpaceOnUse"
                 x1="1139.2747"
                 y1="158.18782"
                 x2="1179.4156"
                 y2="158.18782" /><linearGradient
                 xlink:href="#swatch86"
                 id="linearGradient87"
                 gradientUnits="userSpaceOnUse"
                 x1="1370.3998"
                 y1="328.43546"
                 x2="1415.3529"
                 y2="328.43546" /><linearGradient
                 xlink:href="#swatch12"
                 id="linearGradient88"
                 gradientUnits="userSpaceOnUse"
                 x1="1470.6543"
                 y1="210.92548"
                 x2="1515.3379"
                 y2="210.92548" /><linearGradient
                 xlink:href="#swatch34"
                 id="linearGradient89"
                 gradientUnits="userSpaceOnUse"
                 x1="1660.6543"
                 y1="404.85812"
                 x2="1705.5371"
                 y2="404.85812" /><linearGradient
                 xlink:href="#swatch36"
                 id="linearGradient90"
                 gradientUnits="userSpaceOnUse"
                 gradientTransform="translate(-1.3258253,13.523417)"
                 x1="1639.6543"
                 y1="555.42548"
                 x2="1684.4316"
                 y2="555.42548" /><linearGradient
                 xlink:href="#swatch38"
                 id="linearGradient91"
                 gradientUnits="userSpaceOnUse"
                 x1="1613.7288"
                 y1="137.51672"
                 x2="1658.5471"
                 y2="137.51672" /><linearGradient
                 xlink:href="#swatch39"
                 id="linearGradient92"
                 gradientUnits="userSpaceOnUse"
                 x1="1732.5227"
                 y1="98.452019"
                 x2="1777.2649"
                 y2="98.452019" /></defs><g
               id="g199-8"
               transform="translate(24.783838,-242.80656)"
               style="display:inline;opacity:1;fill:#000000;fill-opacity:1"><g
                 id="g2-8"
                 style="display:inline"><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.933998"
                   id="rect97-2"
                   width="227.4082"
                   height="5.0122862"
                   x="55.007568"
                   y="279.98761" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.914674"
                   id="rect98-4"
                   width="218.0957"
                   height="5.0122862"
                   x="1599.75"
                   y="280" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.704404"
                   id="rect99-5"
                   width="105.94473"
                   height="6.1194272"
                   x="285.59094"
                   y="-282.37561"
                   transform="matrix(1.007684e-4,0.99999999,-0.99999744,0.00226478,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.25163"
                   id="rect100-5"
                   width="14.354056"
                   height="5.7635875"
                   x="276.29074"
                   y="389.23428" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.779729"
                   id="rect101-1"
                   width="10.04142"
                   height="5.1330094"
                   x="50.723171"
                   y="389.67831" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.890399"
                   id="rect102-7"
                   width="179.73141"
                   height="5.763586"
                   x="389.63168"
                   y="-51.489883"
                   transform="matrix(-0.00167324,0.9999986,-0.99999945,-0.00105172,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.680037"
                   id="rect103-1"
                   width="104.83782"
                   height="5.7635746"
                   x="284.81885"
                   y="-60.798965"
                   transform="matrix(-6.8720756e-5,1,-0.99999699,-0.00245323,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.767901"
                   id="rect104-1"
                   width="145.16676"
                   height="5.0057983"
                   x="424.88416"
                   y="565.01416" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.71938"
                   id="rect105-5"
                   width="134.90508"
                   height="5.0122867"
                   x="585.01819"
                   y="565.02106" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.952757"
                   id="rect106-2"
                   width="122.92284"
                   height="9"
                   x="294.23239"
                   y="574.98676" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.777"
                   id="rect107-7"
                   width="9.5548401"
                   height="22.064575"
                   x="416.078"
                   y="413.4165"
                   rx="0.00390625"
                   ry="0.00390625" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.09757"
                   id="rect108-6"
                   width="10.09"
                   height="14.487358"
                   x="425.625"
                   y="421" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.2875"
                   id="rect109-1"
                   width="12"
                   height="14.487358"
                   x="451"
                   y="421" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.260782"
                   id="rect110-4"
                   width="15.417419"
                   height="5.7635875"
                   x="44.999115"
                   y="599.84906" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.920698"
                   id="rect111-2"
                   width="209.82559"
                   height="5.2786512"
                   x="605.15625"
                   y="-61.213383"
                   transform="matrix(-0.00131267,0.99999914,-0.9999991,-0.00134061,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.632097"
                   id="rect112-3"
                   width="55.446583"
                   height="9.4154272"
                   x="544.96692"
                   y="-299.48489"
                   transform="matrix(-0.00886045,0.99996075,-0.99999998,-1.9860353e-4,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:5.84241"
                   id="rect113-2"
                   width="220.4055"
                   height="5.1452813"
                   x="54.679249"
                   y="809.89386" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:6.10867"
                   id="rect114-2"
                   width="5.7639999"
                   height="215.08829"
                   x="275.0788"
                   y="600.00061" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:6.10867"
                   id="rect114-8-1"
                   width="5.7639999"
                   height="215.08829"
                   x="1809.6346"
                   y="599.84979" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.8023"
                   id="rect115-6"
                   width="18.723234"
                   height="5.7639999"
                   x="275.40903"
                   y="600.01318" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.3171"
                   id="rect116-8"
                   width="232.15085"
                   height="9.763587"
                   x="462.79688"
                   y="425.72656" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.2875"
                   id="use116-5"
                   width="12"
                   height="14.487358"
                   x="683"
                   y="421" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.45927"
                   id="rect117-7"
                   width="34.998859"
                   height="5.7412233"
                   x="684.92419"
                   y="559.94348" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:4.05333"
                   id="use117-6"
                   width="12"
                   height="45.487358"
                   x="708"
                   y="390" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.89167"
                   id="rect118-1"
                   width="50.130146"
                   height="10.03748"
                   x="719.90625"
                   y="390" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.8868"
                   id="use118-8"
                   width="50.130146"
                   height="10.012354"
                   x="719.90625"
                   y="580.02509" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.55715"
                   id="use119-9"
                   width="12"
                   height="35.032406"
                   x="707.96875"
                   y="554.97821" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.59884"
                   id="rect119-2"
                   width="4.9754224"
                   height="45.099979"
                   x="765"
                   y="344.875" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:4.97175"
                   id="rect120-7"
                   width="81.817368"
                   height="10.03748"
                   x="769.90558"
                   y="344.875" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.93263"
                   id="use121-9"
                   width="51.190807"
                   height="10.03748"
                   x="931.84924"
                   y="344.875" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:5.06707"
                   id="use122-5"
                   width="84.984474"
                   height="10.03748"
                   x="997.8493"
                   y="344.875" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.60244"
                   id="use123-4"
                   width="4.9754224"
                   height="45.224979"
                   x="1077.8711"
                   y="354.8125" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.89167"
                   id="use124-3"
                   width="50.130146"
                   height="10.03748"
                   x="1081.0223"
                   y="390" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:4.03169"
                   id="use125-1"
                   width="12"
                   height="45.002983"
                   x="1130"
                   y="390" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.44445"
                   id="rect125-2"
                   width="4.9754224"
                   height="13.932255"
                   x="1141"
                   y="421.04272" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.28748"
                   id="use126-3"
                   width="12"
                   height="14.487"
                   x="1161"
                   y="421.03125" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:8.4083"
                   id="rect126-3"
                   width="240.56358"
                   height="9.7639999"
                   x="1170"
                   y="425.7554" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.59451"
                   id="rect127-4"
                   width="4.9754224"
                   height="44.949852"
                   x="770.02209"
                   y="580.02808" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.73411"
                   id="rect128-1"
                   width="49.921593"
                   height="4.9749804"
                   x="775"
                   y="620" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.11985"
                   id="rect129-1"
                   width="65.001831"
                   height="4.9749804"
                   x="820"
                   y="615" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.93472"
                   id="rect130-3"
                   width="4.9754224"
                   height="24.994942"
                   x="880"
                   y="589.98004" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.67071"
                   id="rect131-8"
                   width="89.982376"
                   height="4.9749804"
                   x="885"
                   y="590" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.11997"
                   id="rect132-7"
                   width="4.9754224"
                   height="30.010565"
                   x="975"
                   y="590" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.12013"
                   id="rect133-4"
                   width="65.013214"
                   height="4.9749804"
                   x="979.86743"
                   y="615.03876" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.73558"
                   id="rect134-2"
                   width="49.975422"
                   height="4.9749804"
                   x="1039.979"
                   y="620.01227" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.46411"
                   id="rect135-7"
                   width="4.9754224"
                   height="40.544952"
                   x="1085"
                   y="579.99854" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.863151"
                   id="rect136-7"
                   width="4.9754224"
                   height="4.9749804"
                   x="1090"
                   y="580" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.22184"
                   id="rect137-9"
                   width="4.9754224"
                   height="9.9689217"
                   x="45"
                   y="590.00604" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:4.2423"
                   id="rect138-3"
                   width="59.979324"
                   height="9.9689217"
                   x="1090"
                   y="580.00604" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.57047"
                   id="rect139-1"
                   width="9.9754219"
                   height="22.005909"
                   x="1140"
                   y="557.96906" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.79385"
                   id="rect140-9"
                   width="21.109436"
                   height="5.0645676"
                   x="1149.8232"
                   y="560.05188" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:4.65958"
                   id="rect141-8"
                   width="144.99362"
                   height="4.9749804"
                   x="1300"
                   y="565" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.28747"
                   id="rect142-6"
                   width="12"
                   height="14.487"
                   x="1407.9729"
                   y="421.02188" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.56092"
                   id="rect143-5"
                   width="10.013558"
                   height="8.0840015"
                   x="1435"
                   y="421.0004" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.11008"
                   id="rect144-0"
                   width="10.013558"
                   height="32.092545"
                   x="1445"
                   y="403.39923" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.863151"
                   id="rect145-2"
                   width="4.9754224"
                   height="4.9749804"
                   x="1585"
                   y="565" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.863151"
                   id="rect146-8"
                   width="4.9754224"
                   height="4.9749804"
                   x="1585"
                   y="570" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:6.60801"
                   id="rect147-6"
                   width="144.98256"
                   height="10.00623"
                   x="1444.9672"
                   y="569.95099" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:2.4625"
                   id="rect148-0"
                   width="9.9988594"
                   height="20.148609"
                   x="1579.9741"
                   y="550.07855" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.49748"
                   id="rect149-2"
                   width="14.975422"
                   height="4.9749804"
                   x="1584.9741"
                   y="599.94751" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:3.74236"
                   id="rect150-4"
                   width="4.9754224"
                   height="93.5205"
                   x="1594.9741"
                   y="600.15033" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:4.05199"
                   id="rect151-8"
                   width="4.9754224"
                   height="109.63688"
                   x="1595"
                   y="705.42328" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:1.00899"
                   id="rect152-6"
                   width="131.33456"
                   height="10.093781"
                   x="275.15076"
                   y="188.76599"
                   transform="translate(19.145794,214.59494)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:5.80322"
                   id="rect153-5"
                   width="220.4055"
                   height="5.0764999"
                   x="1595"
                   y="809.98438" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:5.01395"
                   id="rect154-0"
                   width="144.9055"
                   height="5.7639999"
                   x="1140.5"
                   y="565" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:6.39379"
                   id="rect155-9"
                   width="134.55353"
                   height="10.094"
                   x="1432.5"
                   y="188.80936"
                   transform="translate(19.145794,214.59494)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:1.81833"
                   id="rect156-0"
                   width="4.9754224"
                   height="22.078125"
                   x="1585"
                   y="577.89685" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.256638"
                   id="use156-0"
                   width="14.931284"
                   height="5.7635875"
                   x="1810.9991"
                   y="599.84906" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.489136"
                   id="rect157-6"
                   width="54.239269"
                   height="5.7635832"
                   x="389.44016"
                   y="-1828.2258"
                   transform="matrix(-0.00554461,0.99998463,-0.99999995,-3.1738505e-4,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.700103"
                   id="rect158-1"
                   width="111.11613"
                   height="5.763577"
                   x="275.02695"
                   y="-1818.0554"
                   transform="matrix(-7.3230004e-4,0.99999973,-0.99999457,-0.00329585,0,0)" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.593977"
                   id="rect159-3"
                   width="48.960426"
                   height="9.4154272"
                   x="389.32092"
                   y="-1592.9426"
                   transform="matrix(-0.00799206,0.99996806,-0.99999826,0.00186689,0,0)"
                   rx="0"
                   ry="0" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.790221"
                   id="rect160-8"
                   width="141.56349"
                   height="5.7635851"
                   x="458.72131"
                   y="-1827.1439"
                   transform="matrix(-0.00212439,0.99999774,-0.99999966,-8.2837977e-4,0,0)" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:0.703428"
                   id="rect198-9"
                   width="112.17441"
                   height="5.7635841"
                   x="278.92343"
                   y="-1601.5833"
                   transform="matrix(-0.00268096,0.99999641,-0.99999978,-6.5640041e-4,0,0)" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:1.32558"
                   id="rect143-7-3"
                   width="10.013558"
                   height="5.8300986"
                   x="1568.9062"
                   y="171.72266"
                   transform="translate(19.145794,214.59494)" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:1.57059"
                   id="rect39-4"
                   width="14.057319"
                   height="5.8300986"
                   x="1792.8602"
                   y="171.72266"
                   transform="translate(19.145794,214.59494)" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:3.93263"
                   id="rect40-4"
                   width="51.190807"
                   height="10.03748"
                   x="867.06799"
                   y="344.875" /><rect
                   style="fill:#000000;fill-opacity:1;stroke-width:0.576911"
                   id="rect161-6"
                   width="46.187481"
                   height="9.4154272"
                   x="392.52136"
                   y="-294.04306"
                   transform="matrix(8.5606473e-4,0.99999963,-0.99993584,0.01132727,0,0)" /><rect
                   style="display:inline;fill:#000000;fill-opacity:1;stroke-width:2.61992"
                   id="rect107-2-0"
                   width="9.8985901"
                   height="18.956964"
                   x="395.84546"
                   y="350.42877"
                   rx="0.0040467833"
                  ry="0.0033560873"
                  transform="translate(19.145794,214.59494)" /></g>
                  
                  <!-- ROOM1 -->
                  <g
                  id="g176-6"
                  transform="translate(19.145794,214.59494)"
                  style="display:inline"><g
                  id="g1"
                  style="display:inline;opacity:1;fill:#000000;fill-opacity:1"
                  transform="translate(-19.145794,-214.59494)"><path
                  d="M 41.638495,70.406718 41.604,180.19741 70.003906,180.13042 70,123 h 30 V 70.4 Z"
                  style="display:inline;opacity:1;fill:#4396a8;fill-opacity:1"
                  id="room-1"
                  transform="translate(19.145794,214.59494)" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;display:inline;opacity:1;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient57);stroke-width:1;stroke-dasharray:none;stroke-opacity:1;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="69.084595" fill="#FFFFFF"
                  y="317.40634"
                  id="text1"><tspan
                  id="tspan1"
                  x="69.084595"
                  y="317.40634"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient57);stroke-width:1;stroke-dasharray:none;stroke-opacity:1;-inkscape-stroke:hairline">room1</tspan></text></g>
                  
                  <!-- ROOM2 -->
                  <g
                  id="g3"><path
                  style="fill:#ae332c;fill-opacity:1"
                  d="m 31.408128,343.90088 68.897652,-0.002 0.006,-117.7118 -68.869231,-0.008 z"
                  id="room-2" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient58);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="45.872929" fill="#FFFFFF"
                  y="265.7995"
                  id="text2"><tspan
                  id="tspan2"
                  x="45.872929"
                  y="265.7995"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient58);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room2</tspan></text></g>
                  
                  <!-- ROOM3 -->
                  <g
                  id="g20"><path
                  style="display:inline;fill:#134d5b;fill-opacity:1"
                  d="M 156.44922,388.79297 H 41.273437 l -0.269531,206.5136 150.393494,0.004 0.34479,-46.51714 V 448.9028 h -35.3086 z"
                  id="room-3" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient59);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="86.885124" fill="#FFFFFF"
                  y="464.14294"
                  id="text3"><tspan
                  id="tspan3"
                  x="86.885124"
                  y="464.14294"
                  style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient59);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room3</tspan><tspan
                  x="86.885124"
                  y="479.14294"
                 id="tspan4"
                  style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient59);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline" /></text></g>
                  
                  <!-- ROOM4 -->
                  <g
                  id="g21"><path
                  style="fill:#6b4d4d;fill-opacity:1;stroke-width:0.996579"
                  d="m 156.45312,388.79687 h 99.51953 V 595.30063 H 191.39844 L 191.75,448.89922 h -35.30263 z"
                  id="room-4" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient60);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="204.97197" fill="#FFFFFF"
                  y="458.83963"
                  id="text5"><tspan
                  id="tspan5"
                  x="204.97197"
                  y="458.83963"
                  style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient60);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room4</tspan></text></g>
                  
                  <!-- ROOM5 -->
                  <g
                  id="g22"><path
                  style="display:inline;fill:#245b6e;fill-opacity:1"
                  d="m 265.97266,343.78906 v 41.61728 h -10.04688 v 3.39054 l -64.19141,-10e-6 V 361.25 H 246.625 v -17.41933 z"
                  id="room-5" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient61);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="202.32031" fill="#FFFFFF"
                  y="378.40622"
                  id="text6"><tspan
                  id="tspan6"
                  x="202.32031"
                  y="378.40622"
                  style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient61);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room5</tspan></text></g>
                  
                  <!-- ROOM6 -->
                  <g
                  id="g23"><path
                  style="fill:#ae332c;fill-opacity:1"
                  d="M 100.00781,70.402344 V 180.09126 h 39.29223 v 163.69522 h 126.67262 l 0.125,-13.38023 h 9.41406 l -0.26562,29.98686 120.57812,0.004 v -9.97616 h 11 V 268.78906 H 266.125 v -88.38292 h -8.98438 V 70.40625 Z"
                  id="room-6" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient62);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="182.34454" fill="#FFFFFF"
                  y="223.37309"
                  id="text7"><tspan
                  id="tspan7"
                  x="182.34454"
                  y="223.37309"
                  style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient62);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room6</tspan></text></g>
                  
                  <!-- ROOM7 -->
                  <g
                  id="g24"><path
                  style="fill:#6f1b2d;fill-opacity:1"
                  d="m 406.82422,318.68359 v 31.73438 h 36.66175 l -0.004,-16.17743 h -16.61055 v -15.55635 z"
                  id="room-7" /><text
                  xml:space="preserve"
                  style="font-size:11.6517px;text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient63);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="418.78665" fill="#FFFFFF"
                  y="334.10031"
                  id="text8"
                  transform="scale(0.97097739,1.0298901)"><tspan
                  id="tspan8"
                  x="418.78665"
                  y="334.10031"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient63);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room7</tspan></text></g>
                  
                  <!-- ROOM8 -->
                  <g
                  id="g25"><path
                  style="display:inline;fill:#a7b095;fill-opacity:1"
                  d="m 443.89453,273.65234 -0.43359,45.05079 H 426.44898 V 334.25 h 17.03149 v 16.18359 h 107.43105 l -6.9e-4,4.96875 h 14.95972 l 0.003,-4.96875 h 99.46722 v -5.07812 h 23.05279 v -4.97127 h 12.04803 v 25.06502 l 55.43359,-0.0117 v -91.78718 z"
                  id="room-8" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient64);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="559.93958" fill="#FFFFFF"
                  y="311.05432"
                  id="text9"><tspan
                  id="tspan9"
                  x="559.93958"
                  y="311.05432"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient64);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room8</tspan></text></g>
                  
                  <!-- ROOM9 -->
                  <g  
                  id="g26"><path
                  style="fill:#a7b095;fill-opacity:1"
                  d="M 700.875,185.5 H 750.875 V 220.875 H 700.875 Z"
                  id="room-9" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient67);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="708.16687" fill="#FFFFFF"
                  y="206.66769"
                  id="text10"><tspan
                  id="tspan10"
                  x="708.16687"
                  y="206.66769"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient67);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room9</tspan></text></g>
                  
                  <!-- ROOM10 -->
                  <g
                  id="g27"
                  style="display:inline;opacity:1;fill:#000000;fill-opacity:1"><path
                  style="display:inline;fill:#317680;fill-opacity:1;stroke-width:1.07225"
                  d="m 750.82812,140.3125 v 80.57031 h 42.45017 V 140.25 Z"
                  id="room-10" /><text
                  xml:space="preserve"
                  style="font-size:10.8024px;text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient68);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="836.8537" fill="#FFFFFF"
                  y="155.01785"
                  id="text13"
                  transform="scale(0.90019536,1.11087)"><tspan
                  id="tspan13"
                  x="836.8537"
                  y="155.01785"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient68);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room10</tspan></text></g>
                  
                  <!-- ROOM13 -->
                  <g
                  id="g30"
                  style="display:inline;opacity:1;fill:#000000;fill-opacity:1"><path
                  style="fill:#8fa798;fill-opacity:1"
                  d="m 755.87109,273.65234 h 67.55475 V 220.875 h 32.17336 v -37.73434 -7.77818 h 102.53049 v 45.25483 h 33.23401 v 53.38657 H 1060.75 V 266.25 h 66.113 v 7.66549 h 26.9585 V 252 H 1361.25 v 19.73437 l 33.261,-0.006 0.012,44.93751 -16.6911,-0.0536 -0.2209,33.90782 -96.7406,-0.0568 -0.012,4.90211 -14.5976,-0.004 0.016,-4.90696 -114.5193,-0.0672 v -5.01604 h -20.9219 v -2.01027 h -9.981 v 22.04297 h -54.9955 l -0.011,40.01723 h -40.1159 V 400.4375 H 960.82812 V 375.25 h -99.96981 v 25.19341 h -60.02674 v 4.98289 h -44.9722 z"
                  id="room-13" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient76);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="1138.5703" fill="#FFFFFF"
                  y="303.71161"
                  id="text14"><tspan
                  id="tspan14"
                  x="1138.5703"
                  y="303.71161"
                  style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient76);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room13</tspan></text></g>
                  
                  <!-- ROOM11 -->
                  <g
                  id="g28"
                  style="display:inline;opacity:1;fill:#000000;fill-opacity:1"><path
                  style="display:inline;fill:#311b31;fill-opacity:1"
                  d="m 880.64579,488.34494 h 98.25 v 87.40479 h -98.21146 z"
                  id="room-11"
                  transform="translate(-19.145794,-214.59494)" /><text
                  xml:space="preserve"
                  style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient80);stroke-width:1;stroke-dasharray:none;stroke-opacity:1;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="886.41046" fill="#FFFFFF"
                  y="320.22937"
                  id="text12"><tspan
                  id="tspan12"
                  x="886.41046"
                  y="320.22937"
                  style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient80);stroke-width:1;stroke-dasharray:none;stroke-opacity:1;-inkscape-stroke:hairline">room11</tspan></text></g>
                  
                  <!-- ROOM12 -->
                  <g
                  id="g29"
                  style="display:inline;opacity:1;fill:#000000;fill-opacity:1"><path
                  style="display:inline;fill:#317680;fill-opacity:1;stroke-width:1.01308"
                  d="m 1020.8281,140.3125 v 80.57031 h 37.8942 V 140.25 Z"
                  id="room-12" /><text
                  xml:space="preserve"
                  style="font-size:10.7631px;text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient81);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                  x="1138.3025" fill="#FFFFFF"
                  y="162.10048"
                  id="text11"
                  transform="scale(0.89692606,1.1149191)"><tspan
                   id="tspan11"
                   x="1138.3025"
                   y="162.10048"
                   style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient81);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room12</tspan></text></g>
                   
                   <!-- ROOM14 -->
                   <g
                   id="g31"><path
                   style="display:inline;fill:#6f1b2d;fill-opacity:1"
                   d="m 1377.8242,316.68359 h 31.25 v 33.73047 h -31.4726 z"
                   id="room-14" /><text
                   xml:space="preserve"
                   style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient87);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                   x="1369.3158" fill="#FFFFFF"
                   y="332.72159"
                   id="text15"><tspan
                   id="tspan15"
                   x="1369.3158"
                   y="332.72159"
                   style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient87);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room14</tspan></text></g>
                   
                   <!-- ROOM15 -->
                   <g
                   id="g32"><path
                   style="display:inline;fill:#dfbdbe;fill-opacity:1"
                   d="m 1435.8633,198.90234 v 21.83561 l 124.9645,-0.004 0.207,-21.82778 z"
                   id="room-15" /><text
                   xml:space="preserve"
                   style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient88);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                   x="1469.5703" fill="#FFFFFF"
                   y="215.21161"
                   id="text16"><tspan
                   id="tspan16"
                   x="1469.5703"
                   y="215.21161"
                   style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient88);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room15</tspan></text></g>
                   
                   <!-- ROOM16 -->
                   <g
                   id="g33"><path
                   style="fill:#e3d0cc;fill-opacity:1"
                   d="m 1409.0742,316.6875 v 33.86719 h 16.8008 l 0.012,4.85547 h 134.9687 v -19.96875 l 10.0313,-0.0156 v 49.92188 h 9.9063 v 93.71875 l -4.957,0.0117 v 11.75781 l 4.9532,-0.0714 V 595.3867 l 211.1371,0.0117 V 385.15222 h 8.9383 L 1800.5148,274.961 H 1570.307 V 271.5 l -146.1704,0.16016 v 45.10158 z"
                   id="room-16" /><text
                   xml:space="preserve"
                   style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient89);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                   x="1659.5703" fill="#FFFFFF"
                   y="409.21164"
                   id="text17"><tspan
                   id="tspan17"
                   x="1659.5703"
                   y="409.21164"
                   style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient89);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room16</tspan></text></g>
                   
                   <!-- ROOM17 -->
                   <g
                   id="g34"><path
                   style="display:inline;opacity:1;fill:#6f1b2d;fill-opacity:1"
                   d="m 1643.9976,561.72534 31.2499,-0.0784 0.085,33.73037 -31.4725,0.079 z"
                   id="room-17" /><text
                   xml:space="preserve"
                   style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient90);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                   x="1637.2444" fill="#FFFFFF"
                   y="573.23505"
                   id="text18"><tspan
                   id="tspan18"
                   x="1637.2444"
                   y="573.23505"
                   style="vector-effect:non-scaling-stroke;stroke:url(#linearGradient90);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room17</tspan></text></g>
                   
                   <!-- ROOM18 -->
                   <g
                   id="g35"><path
                   style="display:inline;fill:#dfbdbe;fill-opacity:1"
                   d="m 1589.4935,428.384 h 143.9023 V 285.09494 l -132.7852,0.25 -0.073,106.80698 -10.8447,-0.0288 z"
                   id="room-18"
                   transform="translate(-19.145794,-214.59494)" /><text
                   xml:space="preserve"
                   style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient91);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                   x="1612.6448" fill="#FFFFFF"
                   y="141.87903"
                   id="text19"><tspan
                     id="tspan19"
                     x="1612.6448"
                     y="141.87903"
                     style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient91);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room18</tspan></text></g>
                     
                     <!-- ROOM19 -->
                     <g
                   id="g43"><path
                     style="display:inline;fill:#4396a8;fill-opacity:1;stroke-width:1.00238"
                     d="m 1792.8409,177.51812 h -41.2157 v -26.2343 h 11.1822 v -26.21421 h -54.6142 V 70.40132 h 84.7368 z"
                     id="room-19" /><text
                     xml:space="preserve"
                     style="text-align:start;writing-mode:lr-tb;direction:ltr;text-anchor:start;vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient92);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline;paint-order:fill markers stroke"
                     x="1731.4387" fill="#FFFFFF"
                     y="102.81139"
                     id="text20"><tspan
                       id="tspan20"
                       x="1731.4387"
                       y="102.81139"
                       style="vector-effect:non-scaling-stroke;fill:#000000;fill-opacity:1;stroke:url(#linearGradient92);stroke-width:1;stroke-dasharray:none;-inkscape-stroke:hairline">room19</tspan></text></g></g>       

                       <g
                 id="g198-7"
                 style="display:inline;fill:#000000;fill-opacity:1"
                 transform="translate(19.145794,214.59494)"><path
                   style="display:inline;fill:#cc353a;fill-opacity:1;stroke-width:1.23045"
                   d="M 31.6875,180.21978 31.609375,226.19141 69.9375,226.375 V 210.04136 H 47.372752 V 195.11052 H 69.966155 V 180.125 Z"
                   id="path182-6" /><g
                   id="g187-3"
                   style="display:inline;fill:#000000;fill-opacity:1"><path
                     style="fill:#1a1a1a"
                     d="M 261.69922,391.1875 H 292.125 v 204.72674 h -30.4071 z"
                     id="path185-6" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,548.7356 H 292.125 v 47.17519 h -30.4071 z"
                     id="path186-1" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,391.2045 H 292.125 v 47.17519 h -30.4071 z"
                     id="path187-5" /></g><path
                   style="display:inline;fill:#a21815;fill-opacity:1;stroke-width:0.920378"
                   d="m 275.20703,174.59766 h 62.82736 V 171.75 H 359 v 17.05469 h -83.74521 z"
                   id="path197-4" /><path
                   style="fill:#a21815;fill-opacity:1;stroke-width:0.765927"
                   d="m 406.82031,273.72714 v 44.95704 h 37.07422 v -45.03386 z"
                   id="path183-2" /><g
                   id="g187-2-0"
                   style="display:inline;fill:#000000;fill-opacity:1"
                   transform="matrix(0,-1,1.0790689,0,21.793789,647.52548)"><path
                     style="fill:#a7b095;fill-opacity:1"
                     d="M 261.69922,391.1875 H 292.125 v 204.72674 h -30.4071 z"
                     id="path185-3-9" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,548.7356 H 292.125 v 47.17519 h -30.4071 z"
                     id="path186-2-7" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,391.2045 H 292.125 v 47.17519 h -30.4071 z"
                     id="path187-2-3" /></g><path
                   style="display:inline;fill:#cc353e;fill-opacity:1;stroke-width:1.07852"
                   d="m 879.98957,435.46213 h -37.418 v -45.46094 h 37.38165 z"
                   id="path179-7"
                   transform="translate(-19.145794,-214.59494)" /><path
                   style="display:inline;fill:#cc353e;fill-opacity:1;stroke-width:1.07852"
                   d="m 992.57396,220.92719 h -37.418 v -45.46094 h 37.38165 z"
                   id="path179-1-2" /><g
                   id="g193-6"
                   transform="matrix(0,-1,1.1270662,0,722.81417,647.48806)"
                   style="display:inline;fill:#000000;fill-opacity:1"><path
                     style="fill:#8fa798;fill-opacity:1"
                     d="M 261.69922,391.1875 H 292.125 v 204.72674 h -30.4071 z"
                     id="path191-0" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,548.7356 H 292.125 v 47.17519 h -30.4071 z"
                     id="path192-1" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,391.2045 H 292.125 v 47.17519 h -30.4071 z"
                     id="path193-6" /></g><path
                   style="fill:#cf3938;fill-opacity:1;stroke-width:0.74697"
                   d="m 1388.8828,271.72714 v 44.95704 h 35.2617 v -45.03386 z"
                   id="path184-5" /><path
                   style="display:inline;fill:#a21815;fill-opacity:1;stroke-width:0.920378"
                   d="m 1477.4722,174.55347 h 62.8274 v -2.84766 h 20.9656 V 188.7605 H 1477.52 Z"
                   id="path198-7" /><g
                   id="g196-5"
                   transform="translate(1283.7063,-0.86328125)"><path
                     style="fill:#e3d0cc;fill-opacity:1"
                     d="M 261.69922,391.1875 H 292.125 v 204.72674 h -30.4071 z"
                     id="path194-4" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,548.7356 H 292.125 v 47.17519 h -30.4071 z"
                     id="path195-1" /><path
                     style="fill:#971812;fill-opacity:1;stroke-width:0.480031"
                     d="M 261.69922,391.2045 H 292.125 v 47.17519 h -30.4071 z"
                     id="path196-2" /></g><path
                   style="display:inline;fill:#cc353a;fill-opacity:1;stroke-width:1.07233"
                   d="m 1820.2984,392.13109 -0.2187,38.08885 h -34.9339 v -13.49849 h 20.7375 v -12.36178 h -20.4846 v -12.36179 z"
                   id="path181-0"
                   transform="translate(-19.145794,-214.59494)" /></g><path
                 style="display:inline;fill:#cfc7a3;fill-opacity:1"
               d="m 266.11719,220.88281 v 47.91016 h 140.71484 v 4.9375 h 416.59382 v -98.32379 h 167.89446 v 98.60894 h 69.43929 v -7.77343 h 66.131 v 7.68359 h 26.9297 v -21.91797 h 207.4265 v 19.72656 h 209.0735 v 3.23047 h 230.8984 v -29.37109 h 5.8125 l -0.414,-15.9375 h -5.7656 l 0.078,-14.03125 h -34.9375 v -13.50391 h 20.7422 v -12.3734 h -20.4922 v -12.23207 h -13.332 v -26.26106 h 10.8437 V 125.0695 h -49.5116 v 88.73519 l -143.8946,-0.0156 -0.054,6.92187 -144.5429,0.0234 v -6.26004 h -9.9141 v -8.06808 h -15.0132 v 14.53147 h -258.9778 v -14.49569 h -15.0558 v 13.97641 h -15.9414 v -34.97969 h -52.125 v 35.46583 h -37.9257 v -80.60771 h -42.09778 l 0.004,-10.01562 h -14.8125 l 0.008,10.01562 h -51.21875 l 0.0195,-10.00033 h -13.5853 l 0.0267,9.8125 -51.2312,0.18783 v -10.01245 h -15.33538 v 10.0398 h -39.3006 v 80.57031 H 688.82812 V 206.39784 H 675.8615 v 14.5045 H 431.84766 v -14.49609 h -15.27338 v 14.51562 H 396.92187 V 198.85169 H 275.22474 v 21.953 z"
                 id="path39-0"
                 transform="translate(19.145794,214.59494)" /><path
                 style="display:inline;fill:#b9ad79;fill-opacity:1"
                 d="m 25.625,354.8125 0.234116,20.59491 h 4.976822 l -10e-7,9.8465 h 10.4375 v 3.54296 H 191.73437 v -27.55078 h 54.89844 V 343.78906 H 139.28516 V 180.03125 H 99.923027 V 122.98437 H 69.96875 v 72.125 H 47.37063 v 14.94532 H 69.9375 v 16.125 h 30.37223 V 343.89696 H 31.411009 v 10.93116 z"
                 id="path38-1"
                 transform="translate(19.145794,214.59494)" /></g></svg>
          
          </div>
        </div>
      </div>
    </div>
    </main>


      <!-- Details drawer - slides up from bottom -->
      <div class="details-drawer" id="details-drawer">
        <!-- Drawer handle for dragging up/down -->
        <div class="drawer-handle" id="drawer-handle"></div> <!-- <<< This is the handle element -->

        <!-- Location information section -->
        <div class="location-info">
          <!-- Location header with icon and title -->
          <div class="location-header">
            <!-- Location icon - can be replaced with an actual image -->
            <div class="location-icon">
              <!-- Icon placeholder -->
            </div>

            <!-- Location title -->
            <div class="location-title">
              <h2 id="drawer-office-name">Office Name</h2>
            </div>
          </div>

          <!-- Details section with rows of information -->
          <div class="details-section">
            <div class="detail-row" id="drawer-office-details">Details: ...</div>
            <div class="detail-row" id="drawer-office-contact">Contact: ...</div>
            <div class="detail-row" id="drawer-office-location-detail">Location: ...</div>
          </div>
        </div>
      </div>

       <!-- Bottom Navigation Section -->
       <nav class="bottom-nav" role="navigation" aria-label="Visitor navigation">
        <!-- Explore Link -->
        <a href="explore.php" class="active" aria-label="Explore">
          <i class="fas fa-map-marker-alt"></i>
          <span>Explore</span>
        </a>

        <!-- Rooms Link -->
        <a href="rooms.php" aria-label="Rooms">
          <i class="fas fa-building"></i>
          <span>Rooms</span>
        </a>

        <!-- About Link -->
        <a href="about.php" aria-label="About">
          <i class="fas fa-bars"></i>
          <span>About</span>
        </a>
      </nav>

      <!-- Floor Plan JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/svg-pan-zoom@3.6.1/dist/svg-pan-zoom.min.js"></script>
  <script src="mobilePanZoomSetup.js"></script> <!-- Use mobile-specific pan/zoom setup -->

  <script>
    // Make PHP-derived data available globally first
    // This needs to be defined before mobileLabelSetup.js or any script that might use it.
    const officesData = <?php echo json_encode($offices); ?>;
    const highlightOfficeIdFromPHP = <?php echo json_encode($highlight_office_id); ?>;
    
    console.log("Offices Data Loaded (explore.php - global init):", officesData ? officesData.length : 0, "offices");
    console.log("Office to highlight from QR (ID - global init):", highlightOfficeIdFromPHP);
  </script>

      <script>
        document.addEventListener("DOMContentLoaded", function () {
          // Get references to elements
          const detailsDrawer = document.getElementById("details-drawer");
          const drawerHandle = document.getElementById("drawer-handle");
          const mainContent = document.querySelector("main.content"); // Get the main content element

          // --- Basic Checks ---
          if (!detailsDrawer || !drawerHandle || !mainContent) {
            console.error("Drawer, handle, or main content element not found!");
            return; // Stop if essential elements are missing
          }
          // --- End Basic Checks ---

          // Drawer state tracking
          let isDragging = false;
          let startY = 0;
          let startTranslate = 0;

          // Calculate initial drawer state and dimensions
          const drawerHeight = detailsDrawer.offsetHeight;
          const minTranslate = 0; // Fully open state (translateY = 0)
          const handleHeight = 40; // Approximate height of the handle area visible when closed
          const maxTranslate = drawerHeight - handleHeight; // Mostly closed state (only handle showing)
          let currentTranslate = calculateTranslateY(detailsDrawer); // Get initial position

          // Threshold to determine if drawer should snap open or closed
          const snapThreshold = drawerHeight * 0.4; // Snap open if dragged more than 40% up

          // Function to update main content height based on drawer position
          function adjustMainContentHeight(translateY) {
            if (mainContent) {
              // Calculate how much vertical space the drawer occupies *above the bottom nav*
              const navHeight = 60; // Assuming bottom nav is 60px
              const occupiedDrawerHeight = Math.max(0, drawerHeight - translateY - navHeight);
              // Calculate the new height for the main content area
              // Viewport height - header height - nav height - occupied drawer height
              const headerHeight = 80; // Assuming header is 80px
              const newMainHeight = `calc(100vh - ${headerHeight}px - ${navHeight}px - ${occupiedDrawerHeight}px - -85px)`; // <<< Added 20px gap
              mainContent.style.height = newMainHeight;
              // console.log(`Adjusting main content height. Drawer Occupied: ${occupiedDrawerHeight}px, New Height: ${newMainHeight}`); // Optional debug log
            }
          }

          // Function to open the drawer fully (callable from other scripts)
          window.openDrawer = function() {
            detailsDrawer.style.transition = "transform 0.2s ease";
            detailsDrawer.style.transform = `translateY(${minTranslate}px)`;
            currentTranslate = minTranslate;
            // console.log("Drawer opened programmatically."); // Less verbose for now
            adjustMainContentHeight(currentTranslate); // Update height when opened
          }

          // Handle starting a drag
          function handleDragStart(e) {
            isDragging = true;
            startY = getClientY(e); // Get initial touch/mouse position
            startTranslate = calculateTranslateY(detailsDrawer); // Get current drawer position
            detailsDrawer.classList.add("dragging"); // Add class to disable transitions

            // Add event listeners for move and end events
            if (e.type === "mousedown") {
              document.addEventListener("mousemove", handleDragMove);
              document.addEventListener("mouseup", handleDragEnd);
            } else if (e.type === "touchstart") {
              document.addEventListener("touchmove", handleDragMove, { passive: false });
              document.addEventListener("touchend", handleDragEnd);
            }
            e.preventDefault(); // Prevent text selection/page scroll during drag
          }

          // Handle drag movement
          function handleDragMove(e) {
            if (!isDragging) return;

            const currentY = getClientY(e);
            const deltaY = currentY - startY; // How far we've moved
            let newTranslate = startTranslate + deltaY; // Calculate new position

            // Clamp the translation within bounds (minTranslate to maxTranslate)
            newTranslate = Math.max(minTranslate, Math.min(maxTranslate, newTranslate));

            // Apply new position directly (no transition during drag)
            detailsDrawer.style.transform = `translateY(${newTranslate}px)`;
            currentTranslate = newTranslate; // Store current position

            adjustMainContentHeight(currentTranslate); // Update main content height during drag
            e.preventDefault(); // Prevent scrolling while dragging drawer
          }

          // Handle end of drag
          function handleDragEnd(e) {
            if (!isDragging) return;
            isDragging = false;
            detailsDrawer.classList.remove("dragging"); // Re-enable transitions

            // Snap to open or closed position based on threshold
            // Snap open if it's dragged further up than (maxTranslate - snapThreshold)
            const snappedPosition = (currentTranslate < (maxTranslate - snapThreshold)) ? minTranslate : maxTranslate;

            // Apply smooth transition to final snapped position
            detailsDrawer.style.transition = "transform 0.2s ease";
            detailsDrawer.style.transform = `translateY(${snappedPosition}px)`;
            currentTranslate = snappedPosition;
            adjustMainContentHeight(currentTranslate); // Update height after snap

            // Remove move and end event listeners
            document.removeEventListener("mousemove", handleDragMove);
            document.removeEventListener("mouseup", handleDragEnd);
            document.removeEventListener("touchmove", handleDragMove);
            document.removeEventListener("touchend", handleDragEnd);
          }

          // Handle click on the handle to toggle drawer
          function handleClick() {
            // Toggle between open (minTranslate) and closed (maxTranslate) positions
            const newPosition = (currentTranslate === minTranslate) ? maxTranslate : minTranslate;
            detailsDrawer.style.transition = "transform 0.2s ease";
            detailsDrawer.style.transform = `translateY(${newPosition}px)`;
            currentTranslate = newPosition;
            adjustMainContentHeight(currentTranslate); // Update height on click toggle
          }

          // Helper function to get clientY from mouse or touch events
          function getClientY(e) {
            return e.type.includes("touch") ? e.touches[0].clientY : e.clientY;
          }

          // Helper function to calculate the current translateY value from the transform style
          function calculateTranslateY(element) {
            const transform = window.getComputedStyle(element).getPropertyValue("transform");
            if (transform === "none") return maxTranslate; // Default to closed if no transform
            const matrix = transform.match(/^matrix\((.+)\)$/);
            return matrix ? parseFloat(matrix[1].split(", ")[5]) : maxTranslate;
          }

          // Add event listeners for dragging and clicking the handle
          drawerHandle.addEventListener("mousedown", handleDragStart);
          drawerHandle.addEventListener("touchstart", handleDragStart, { passive: false });
          drawerHandle.addEventListener("click", handleClick); // Add click listener too

          // Set initial main content height based on initial drawer state
          adjustMainContentHeight(currentTranslate);

          // Navigation functionality
          const navLinks = document.querySelectorAll(".bottom-nav a");

          navLinks.forEach((link) => {
            link.addEventListener("click", function (e) {
              // Remove active class from all links
              navLinks.forEach((l) => l.classList.remove("active"));

              // Add active class to clicked link
              this.classList.add("active");

              // Optional: prevent default behavior if you want to handle navigation in JS
              // e.preventDefault();

              // You can add custom navigation logic here
              const section = this.getAttribute("href").substring(1);
              console.log("Navigating to:", section);

              // Example: Show/hide different content based on navigation
              if (section === "explore") {
                // Show explore content
              } else if (section === "rooms") {
                // Show rooms content
              } else if (section === "about") {
                // Show about content
              }
            });
          });

          // --- Logic for QR Code Office Highlight ---
          // This uses officesData and highlightOfficeIdFromPHP defined in the script tag above this one.

          function populateAndShowDrawerWithData(office) {
            console.log("Attempting to populate and show drawer with data:", office);
            if (!office) {
                console.warn("populateAndShowDrawerWithData: No office data provided.");
                return;
            }

            const drawerOfficeNameEl = document.getElementById("drawer-office-name");
            const drawerOfficeDetailsEl = document.getElementById("drawer-office-details");
            const drawerOfficeContactEl = document.getElementById("drawer-office-contact");
            const drawerOfficeLocationEl = document.getElementById("drawer-office-location-detail");

            if (!detailsDrawer || !drawerOfficeNameEl || !drawerOfficeDetailsEl || !drawerOfficeContactEl || !drawerOfficeLocationEl) {
                console.error("One or more drawer elements are missing from the DOM for QR display.");
                return;
            }

            drawerOfficeNameEl.textContent = office.name || 'N/A';
            drawerOfficeDetailsEl.textContent = 'Details: ' + (office.details || 'No details available.');
            drawerOfficeContactEl.textContent = 'Contact: ' + (office.contact || 'No contact info.');
            drawerOfficeLocationEl.textContent = 'Location: ' + (office.location || 'No location info.');
            
            if (window.openDrawer) { // Ensure openDrawer function is available
                console.log("Calling window.openDrawer() from populateAndShowDrawerWithData.");
                window.openDrawer();
            } else {
                console.error("window.openDrawer is not available. Cannot open drawer for QR office.");
            }
          }

          console.log("DOM Content Loaded. Checking for highlightOfficeIdFromPHP:", highlightOfficeIdFromPHP);
          console.log("DOM Content Loaded. Checking officesData:", officesData ? `Available with ${officesData.length} items` : "Not available or empty");

          if (highlightOfficeIdFromPHP !== null && typeof officesData !== 'undefined' && officesData && officesData.length > 0) {
              console.log("QR Code: Proceeding to find office with ID:", highlightOfficeIdFromPHP);
              const officeToHighlight = officesData.find(office => Number(office.id) === highlightOfficeIdFromPHP);
              if (officeToHighlight) {
                  console.log("QR Code: Found office to highlight:", officeToHighlight);
                  setTimeout(() => {
                      console.log("QR Code: setTimeout triggered. Calling populateAndShowDrawerWithData.");
                      populateAndShowDrawerWithData(officeToHighlight);
                  }, 300); // 300ms delay to ensure UI is ready
              } else {
                  console.warn("QR Code: Office ID", highlightOfficeIdFromPHP, "not found in officesData.");
                  console.log("Available office IDs in officesData:", officesData.map(o => o.id));
              }
          } else if (highlightOfficeIdFromPHP !== null) {
              console.warn("QR Code: officesData is not defined, empty, or highlightOfficeIdFromPHP is null. Cannot highlight office from QR.");
          }
        });
      </script>
  <script src="mobileLabelSetup.js"></script> <!-- Use the mobile-specific setup -->

    </body>
  </html>
