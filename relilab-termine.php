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
    ?>
    <ul>
    <?php
    foreach ($posts as $post) {
        setup_postdata( $post );
        $date = get_post_meta($post->ID, 'relilab_startdate',true);
        $dateend = get_post_meta($post->ID, 'relilab_enddate',true);
        $date = date('d.m.Y H:i', strtotime($date));
        $dateend = date('H:i', strtotime($dateend));
        ?>
        <li>
            <span class="Datum"> <?php echo getWochentag($date) . ' ' . $date .' - '. $dateend .' Uhr' ; ?> </span>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </li>
        <?php
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
