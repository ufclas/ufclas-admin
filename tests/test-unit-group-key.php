<?php
/**
 * Standalone test for the title-based unit_group_id.
 * Run: php ufclas-admin/tests/test-unit-group-key.php
 * Shims the few WP functions the file touches so the classifier loads without WordPress.
 */

function add_action() {}                       // no-op so top-level hook registrations load
function check_ajax_referer() {}
function sanitize_title( $t ) {                // simplified WP slug: lowercase, non-alnum -> hyphen
    $t = strtolower( strip_tags( (string) $t ) );
    $t = preg_replace( '/[^a-z0-9]+/', '-', $t );
    return trim( $t, '-' );
}

require __DIR__ . '/../ufclas-admin-info.php';

function assert_eq( $got, $want, $msg ) {
    if ( $got !== $want ) {
        fwrite( STDERR, "FAIL: $msg (got " . var_export( $got, true ) . ", want " . var_export( $want, true ) . ")\n" );
        exit( 1 );
    }
    echo "PASS: $msg\n";
}

// Unmigrated pair: same title, live (.ufl.edu + uf-clas-dept) + staged (-mercury).
$pair = [
    10 => [ 'id' => 10, 'path' => 'https://anthro.ufl.edu',                 'title' => 'Anthropology', 'status' => 'public', 'theme' => 'uf-clas-dept' ],
    11 => [ 'id' => 11, 'path' => 'https://portal.clas.ufl.edu/anthro-mercury', 'title' => 'Anthropology', 'status' => 'public', 'theme' => 'twentytwentyone' ],
];
$c = ufclas_admin_classify_sites( $pair );
assert_eq( $c[10]['unit_group_id'], $c[11]['unit_group_id'], 'Unmigrated pair shares one key' );
assert_eq( $c[10]['unit_group_id'], 'g-anthropology', 'key is the title slug' );

// Pairing broken (go-live): live site theme no longer uf-clas-dept -> both Unclassified, own blog ids.
$broken = $pair;
$broken[10]['theme'] = 'twentytwentyfour';
$cb = ufclas_admin_classify_sites( $broken );
assert_eq( $cb[10]['unit_group_id'], $cb[11]['unit_group_id'], 'broken pairing still shares the title key' );
assert_eq( $cb[10]['unit_group_id'], 'g-anthropology', 'title key survives the pairing break' );

// Empty-title fallback.
$blank = [ 5 => [ 'id' => 5, 'path' => 'https://example.org/x', 'title' => '', 'status' => 'public', 'theme' => '' ] ];
$cbl = ufclas_admin_classify_sites( $blank );
assert_eq( $cbl[5]['unit_group_id'], 'g-5', 'empty title falls back to blog id' );

echo "ALL PASS\n";
