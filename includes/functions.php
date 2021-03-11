<?php
// Common PHP functions for the website

// Pull out YAML front-matter from a markdown file
require_once(dirname(__FILE__) . '/libraries/Spyc.php');
function parse_md_front_matter($md_full)
{
  if (substr($md_full, 0, 3) == '---') {
    $md_parts = explode('---', $md_full, 3);
    if (count($md_parts) == 3) {
      $meta = spyc_load($md_parts[1]);
      $md = $md_parts[2];
      return array(
        'meta' => $meta,
        'md' => $md
      );
    }
  }
  return array(
    'meta' => null,
    'md' => $md_full
  );
}

// Helper function for event page
function sanitise_date_meta($event)
{
  # Check that start date is set, delete if not
  if (!isset($event['start_date'])) {
    return false;
  }
  # Check end date is set
  if (!isset($event['end_date'])) {
    $event['end_date'] = $event['start_date'];
  }
  # Parse dates
  if (!isset($event['start_time'])) $event['start_time'] = '';
  if (!isset($event['end_time'])) $event['end_time'] = '';
  $event['start_ts'] = strtotime($event['start_date'] . ' ' . $event['start_time']);
  $event['end_ts'] = strtotime($event['end_date'] . ' ' . $event['end_time']);
  # Check end is after start
  if ($event['end_ts'] < $event['start_ts']) {
    $event['end_date'] = $event['start_date'];
    $event['end_ts'] = strtotime($event['end_date'] . ' ' . $event['end_time']);
  }
  return $event;
}

function print_events($events, $is_past_event, $is_current_event)
{
  $event_type_classes = array(
    'hackathon' => 'primary',
    'talk' => 'success',
    'poster' => 'secondary',
    'tutorial' => 'info',
    'workshop' => 'light'
  );
  $event_type_icons = array(
    'hackathon' => 'fas fa-laptop-code',
    'talk' => 'fas fa-presentation',
    'poster' => 'far fa-image',
    'tutorial' => 'fas fa-graduation-cap',
    'workshop' => 'fas fa-chalkboard-teacher'
  );
  foreach ($events as $idx => $event) :
    # Nice date strings
    $date_string = date('j<\s\u\p>S</\s\u\p> M Y', $event['start_ts']) . ' - ' . date('j<\s\u\p>S</\s\u\p> M Y', $event['end_ts']);
    if (date('mY', $event['start_ts']) == date('mY', $event['end_ts'])) {
      $date_string = date('j<\s\u\p>S</\s\u\p> ', $event['start_ts']) . ' - ' . date('j<\s\u\p>S</\s\u\p> M Y', $event['end_ts']);
    }
    if (date('dmY', $event['start_ts']) == date('dmY', $event['end_ts'])) {
      $date_string = date('j<\s\u\p>S</\s\u\p> M Y', $event['end_ts']);
    }
    $colour_class = $event_type_classes[strtolower($event['type'])];
    $icon_class = $event_type_icons[strtolower($event['type'])];
?>

    <!-- Event Card -->
    <?php if ($is_current_event) {
      echo '<div class="card my-4 border-' . $colour_class . '">';
    } else {
      echo '<div class="card my-4 border-top-0 border-right-0 border-bottom-0 border-' . $colour_class . '">';
    }
    ?>

    <div class="card-body <?php if ($is_past_event) {
                            echo 'py-2';
                          } ?>">
      <h5 class="my-0 py-0">
        <a class="text-success" href="<?php echo $event['url']; ?>"><?php echo $event['title']; ?></a>
        <?php if ($is_current_event) {
          echo '<small><span class="badge badge-' . $colour_class . ' small"><i class="' . $icon_class . ' mr-1"></i>' . ucfirst($event['type']) . '</span></small>';
        } else {
          echo '<small><span class="badge badge-' . $colour_class . ' float-right small"><i class="' . $icon_class . ' mr-1"></i>' . ucfirst($event['type']) . '</span></small>';
        } ?>

      </h5>
      <?php if (array_key_exists('subtitle', $event)) {
        $tm = $is_past_event ? 'text-muted' : '';
        echo '<p class="mb-0 ' . $tm . '">' . $event['subtitle'] . '</p>';
      }
      if (!$is_past_event) : ?>
        <h6 class="small text-muted"><?php echo $date_string; ?></h6>
        <?php if (array_key_exists('description', $event)) {
          echo '<p>' . nl2br($event['description']) . '</p>';
        } ?>
        <a href="<?php echo $event['url']; ?>" class="btn btn-outline-success">
          See details
        </a>
      <?php else : ?>
        <h6 class="small text-muted mb-0">
          <?php echo $date_string; ?> -
          <a class="text-success" href="<?php echo $event['url']; ?>">
            See details
          </a>
        </h6>
      <?php endif; ?>
    </div>
    <?php if ($is_current_event) {
      if (array_key_exists('location_url', $event) && $event['location_url'][0] != "#") {
        echo '<a href="'.$event['location_url'][0]. '" class="btn btn-' . $colour_class . ' rounded-0"><i class="fad fa-external-link mr-1"></i>' . $event['location_url'][0] . '</a>';
      }
      echo '<div class="bg-icon"><i class="' . preg_replace('/fas|far/', 'fad', $icon_class) . '  text-' . $colour_class . '"></i></div>';
    }

    ?>
    </div>


<?php
  endforeach;
}


// From https://stackoverflow.com/a/18891474/713980
function time_ago($date, $ago = true)
{
  $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
  $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
  $now = time();
  if (is_numeric($date)) $unix_date = $date;
  else $unix_date = strtotime($date);
  // check validity of date
  if (empty($unix_date)) {
    return $date;
  }
  // is it future date or past date
  if ($now > $unix_date) {
    $difference = $now - $unix_date;
    $tense = "ago";
  } else {
    $difference = $unix_date - $now;
    $tense = "from now";
  }
  for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
    $difference /= $lengths[$j];
  }
  $difference = round($difference);
  if ($difference != 1) {
    $periods[$j] .= "s";
  }
  $returnstring = "$difference $periods[$j]";
  if ($ago || (!$ago && $tense != 'ago')) {
    $returnstring .= " {$tense}";
  }
  return $returnstring;
}


function rsort_releases($a, $b)
{
  $t1 = strtotime($a->published_at);
  $t2 = strtotime($b->published_at);
  return $t2 - $t1;
}
function rsort_pipelines($a, $b)
{
  $t1 = strtotime($a->last_release);
  $t2 = strtotime($b->last_release);
  return $t2 - $t1;
}

function round_nicely($num)
{
  if ($num > 1000000) {
    $num /= 1000000;
    $num = round($num, 2) . 'M';
  } else if ($num > 1000) {
    $num /= 1000;
    $num = round($num, 2) . 'K';
  }
  return $num;
}

function endswith($haystack, $needle)
{
  $length = strlen($needle);
  if (!$length) {
    return true;
  }
  return substr($haystack, -$length) === $needle;
}

function return_json($response)
{
  // Spit out a JSON response with correct headers and exit
  header('Content-type: application/json');
  echo json_encode($response, JSON_PRETTY_PRINT);
  exit;
}

function get_self_url($strip_query = true)
{
  // Build URL for this page
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $self_url = "https://";
  else $self_url = "http://";
  if ($strip_query) {
    $url = strtok($_SERVER["REQUEST_URI"], '?');
  } else {
    $url = $_SERVER["REQUEST_URI"];
  }
  return $self_url . $_SERVER['HTTP_HOST'] . $url;
}

function generate_toc($html_string)
{
  $toc = '';
  $curr_level = 0;
  $counter = 0;
  $id_regex = "~<h([1-3])([^>]*)id\s*=\s*['\"]([^'\"]*)['\"]([^>]*)>(.*)</h[1-3]>~Uis";
  preg_match_all($id_regex, $html_string, $matches, PREG_SET_ORDER);
  if ($matches) {
    foreach ($matches as $match) {
      $whole_str = $match[0];
      $level = $match[1];
      $before_attrs = trim($match[2]);
      $id = trim($match[3]);
      $after_attrs = trim($match[4]);
      $h_content = $match[5];
      $name = trim(str_replace('&nbsp;', '', htmlentities(strip_tags($h_content))));
      if ($level > $curr_level) {
        $toc .= "\n" . '<div class="list-group">' . "\n";
        $counter += 1;
      } else if ($level == $curr_level) {
        $toc .= "\n";
      } else {
        while ($level < $counter) {
          $toc .= "\n</div>\n\n";
          $counter -= 1;
        }
      }
      $curr_level = $level;
      if (preg_match('/<code>.*?<\/code>/', $whole_str)) {
        $name = '<code>' . $name . '</code>';
      }
      if (preg_match('/<i.*?<\/i>/', $whole_str, $icon_match)) {
        $name = $icon_match[0] . $name;
      }
      $is_hidden = strpos($before_attrs, 'toc-hidden') !== false || strpos($after_attrs, 'toc-hidden') !== false;
      $toc_hidden = $is_hidden ? 'collapse' : '';
      $toc .= '<a class="list-group-item list-group-item-action scroll_to_link ' . $toc_hidden . '" href="#' . $id . '">' . $name . '</a>';
    }
  }
  while ($counter > 0) {
    $toc .= '</div>';
    $counter -= 1;
  }
  return $toc;
}

$heading_ids = [];
function _h($level, $html, $toc_hidden=false){
  ////////////////
  // Build a heading tag with ID and anchor link
  ////////////////
  global $heading_ids;
  # Clean up the ID
  $hid = trim(strip_tags($html));
  $hid = strtolower( preg_replace('/[^\w\-\.]/', '', str_replace(' ', '-', $hid)));
  # Avoid duplicate IDs
  $i = 1; $base_hid = $hid;
  while(in_array($hid, $heading_ids)){
    $hid = $base_hid.'-'.$i;
    $i += 1;
  }
  # Class for hiding in ToC
  $toc_hidden_class = $toc_hidden ? 'toc-hidden' : '';
  return '
    <h'.$level.' id="'.$hid.'" class="'.$toc_hidden_class.'">
      <a href="#'.$hid.'" class="header-link"><span class="fas fa-link"></span></a>
      '.$html.'
    </h'.$level.'>';
};
function _h1($html){ return _h(1, $html); }
function _h2($html){ return _h(2, $html); }
function _h3($html){ return _h(3, $html); }
function _h4($html){ return _h(4, $html); }
function _h5($html){ return _h(5, $html); }


function add_ids_to_headers($content_input, $is_hidden=false){
  //////////////////
  // Add IDs and anchor links to all headings in a block of HTML
  //////////////////
  global $heading_ids;
  $content_output = preg_replace_callback(
    '~<h([1234])>(.*?)</h([1234])>~Ui', // Ungreedy by default, case insensitive
    function ($matches) use($heading_ids, $is_hidden) {
      $id_match = trim(strip_tags($matches[2]));
      $id_match = strtolower( preg_replace('/[^\w\-\.]+/', '', str_replace(' ', '-', $id_match)));
      $hid = $id_match;
      $i = 1;
      while(in_array($hid, $heading_ids)){
        $hid = $id_match.'-'.$i;
        $i += 1;
      }
      $hid = preg_replace('/^[\s\-]+/','', $hid); // remove dashes from start of string (e.g. for parameter)
      $heading_ids[] = $hid;
      $hidden_class = $is_hidden ? 'toc-hidden' : '';
      return '<h'.$matches[1].' id="'.$hid.'" class="'.$hidden_class.'"><a href="#'.$hid.'" class="header-link scroll_to_link"><span class="fas fa-link"></span></a>'.$matches[2].'</h'.$matches[3].'>';
    },
    $content_input
  );
  return $content_output;
}
