<?php
/**
 *Plugin Name: relilab Termine
 */
add_shortcode('relilab_termine','termineAusgeben');

function   termineAusgeben( $atts ) {

    $posts = get_posts(array(
        'post_type'			=> 'post',
        'posts_per_page'	=> -1,
        'category'          => 'termine',
        'meta_key'			=> 'relilab_startdate',
        'orderby'			=> 'meta_value',
        'order'				=> 'ASC'
    ));
    global $post;
    $lastPostMonth = '';
    ?>
    <ul>
    <?php
    foreach ($posts as $post) {
        setup_postdata( $post );
        if (!empty(get_post_meta($post->ID, 'relilab_startdate',true)) && !empty(get_post_meta($post->ID, 'relilab_enddate',true)))
        {
            if ($template = locate_template('relilab-Termine'))
                load_template($template);
            else
//            load_template('templates/single_termin_block.php');
                include 'templates/single-termin-block.php';
        }
    }
    ?>

        </ul>
    <?php
    wp_reset_postdata();
}

/**
 * @param string $date
 * @return string
 */
function getWochentag(string $date): string
{
    $wochentag = array(     'Mon' => 'Montag',
                            'Tue' => 'Dienstag',
                            'Wed' => 'Mittwoch',
                            'Thu' => 'Donnerstag',
                            'Fri' => 'Freitag',
                            'Sat' => 'Samstag',
                            'Sun' => 'Sonntag',
        );
    return $wochentag[date('D',strtotime($date))];
}

/**
 * @param string $date
 * @return string
 */
function getMonat(string $date): string
{
    $monat = array(
            'Jan'   => 'Januar',
            'Feb'   => 'Februar',
            'Mar'   => 'März',
            'Apr'   => 'April',
            'May'   => 'Mai',
            'Jun'   => 'Juni',
            'Jul'   => 'Juli',
            'Aug'   => 'August',
            'Sep'   => 'September',
            'Okt'   => 'Oktober',
            'Nov'   => 'November',
            'Dec'   => 'Dezember',
    );
    if(!empty($date))
        return $monat[date('M',strtotime($date))];
    else
        return '';
}
