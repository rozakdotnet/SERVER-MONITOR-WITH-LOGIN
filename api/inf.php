<?php
header('Content-Type: application/json; charset=UTF-8'); $iniFile = __DIR__. '/../conf/conf.ini'; $config = parse_ini_file($iniFile, true);
$ImmUrl = $config['Immich']['url'] ?? 'unknown'; $ImmKey = $config['Immich']['key'] ?? 'unknown';
function getCpuTemp() {
  foreach(glob('/sys/class/hwmon/hwmon*/name') as $file) {
    if (trim(file_get_contents($file)) === 'coretemp') {
      $dir = dirname($file);
      $temp = file_get_contents($dir. '/temp1_input');
      return round($temp / 1000, 1);
    }
  }
  return null;
}
function getAcpiTemp() {
  foreach(glob('/sys/class/thermal/thermal_zone*/type') as $file) {
    if (strpos(strtolower(file_get_contents($file)), 'acpi') !== false) {
      $zone = dirname($file). '/temp';
      if (is_readable($zone)) {
        return round(file_get_contents($zone) / 1000, 1);
      }
    }
  }
  return null;
}
function getMountedDisksWithModel() {
  $disks = [];
  if (!is_readable("/proc/mounts")) return $disks;
  $diskModels = [];
  foreach(glob("/sys/block/sd*") as $block) {
    $name = basename($block);
    $model = is_readable("$block/device/model") ? trim(file_get_contents("$block/device/model")) : "Unknown";
    $serial = is_readable("$block/device/serial") ? trim(file_get_contents("$block/device/serial")) : "Unknown";
    $diskModels[$name] = ['model' => $model, 'serial' => $serial];
  }
  $ignoreFs = ["proc", "sysfs", "tmpfs", "devtmpfs", "devpts", "cgroup", "cgroup2",
    "overlay", "squashfs", "rpc_pipefs", "tracefs", "securityfs", "autofs"];
  foreach(file("/proc/mounts") as $line) {
    $parts = preg_split('/\s+/', $line);
    if (count($parts) < 3) continue;
    $device = $parts[0];
    $mount = $parts[1];
    $fs = $parts[2];
    if (in_array($fs, $ignoreFs)) continue;
    if (strpos($device, "loop") !== false) continue;
    if (strpos($mount, "/snap") === 0) continue;
    if (strpos($mount, "/var/lib/docker") === 0) continue;
    if (!is_dir($mount)) continue;
    $total = @disk_total_space($mount);
    $free = @disk_free_space($mount);
    if (!$total) continue;
    $used = $total - $free;
    $devName = null;
    if (preg_match('#/dev/(sd[a-z]+)#', $device, $matches)) {
      $devName = $matches[1];
    }
    $disks[] = [
      "mount"   => $mount,
      "total"   => $total,
      "used"    => $used,
      "free"    => $free,
      "percent" => round(($used / $total) * 100, 1),
      "model"   => $devName && isset($diskModels[$devName]) ? $diskModels[$devName]['model'] : "Unknown",
      "serial"  => $devName && isset($diskModels[$devName]) ? $diskModels[$devName]['serial'] : "Unknown"
    ];
  }
  return $disks;
}
function getServerInfo() {
  $hostname = gethostname();
  $os = php_uname('s');
  $kernel = php_uname('r');
  $distro = "Unknown";
  if (is_readable("/etc/os-release")) {
    foreach(file("/etc/os-release") as $line) {
      if (strpos($line, "PRETTY_NAME=") === 0) {
        $distro = trim(explode("=", $line)[1], "\" \n");
        break;
      }
    }
  }
  $cpuModel = "Unknown";
  if (is_readable("/proc/cpuinfo")) {
    foreach(file("/proc/cpuinfo") as $line) {
      if (stripos($line, "model name") !== false) {
        $cpuModel = trim(explode(":", $line)[1]);
        break;
      }
    }
  }
  $cpuCores = (int) shell_exec("nproc");
  $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? "Unknown";
  $phpVersion = PHP_VERSION;
  $extensions = get_loaded_extensions();
  $serverIP = $_SERVER['SERVER_ADDR'] ?? getHostByName(getHostName());
  function getImmichStats() {
    global $ImmUrl;
    global $ImmKey;
    $url = $ImmUrl."/api/server/statistics";
    $apiKey = $ImmKey;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        "x-api-key: $apiKey"
      ],
      CURLOPT_TIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    if (!$response) {
      return [
        "photos" => ["count"=> 0, "size_gb"=> 0],
        "videos" => ["count"=> 0, "size_gb"=> 0],
        "total_gb" => 0
      ];
    }
    $data = json_decode($response, true);
    $photosCount = $data['photos'] ?? 0;
    $videosCount = $data['videos'] ?? 0;
    $photosSize = $data['usagePhotos'] ?? 0;
    $videosSize = $data['usageVideos'] ?? 0;
    $totalSize = $data['usage'] ?? 0;
    return [
      "photos" => [
        "count" => $photosCount,
        "size_gb" => round($photosSize / 1024 / 1024 / 1024, 2)
      ],
      "videos" => [
        "count" => $videosCount,
        "size_gb" => round($videosSize / 1024 / 1024 / 1024, 2)
      ],
      "total_gb" => round($totalSize / 1024 / 1024 / 1024, 2)
    ];
  }
  return [
    "hostname"=> $hostname,
    "os"=> $os,
    "kernel"=> $kernel,
    "distro"=> $distro,
    "server_ip"=> $serverIP,
    "immich" => getImmichStats(),
    "cpu"=> [
      "model"=> $cpuModel,
      "cores"=> $cpuCores,
    ],
    "storage"=> [
      "all"=> getMountedDisksWithModel()
    ],
    "server"=> [
      "software"=> $serverSoftware,
      "php_version"=> $phpVersion,
      "extensions"=> $extensions
    ]
  ];
}
echo json_encode(getServerInfo());
