<?php
$urls = [
    'Formal 1' => 'https://images.unsplash.com/photo-1478145046317-39f10e56b5e9?auto=format&fit=crop&w=1000&q=80',
    'Formal 2' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=1000&q=80',
    'Sneaker 1' => 'https://images.unsplash.com/photo-1560769629-975e13f0c470?auto=format&fit=crop&w=1000&q=80',
    'Sneaker 2' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80'
];

foreach ($urls as $name => $url) {
    $headers = @get_headers($url);
    if($headers && strpos($headers[0], '200')) {
        echo "$name: VALID\n";
    } else {
        echo "$name: INVALID (" . ($headers[0] ?? 'Failed') . ")\n";
    }
}
?>
