<?php
/**
 *Plugin Name: relilab Termine
 */

class RelilabTermine{
    public function __construct(){
        add_shortcode('relilab_termine',array($this,'termineAusgeben'));
        add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts'));
    }
    function   termineAusgeben( $atts )
    {
        $posts = get_posts(array(
            'post_type'			=> 'post',
            'posts_per_page'	=> -1,
            'category'          => 'termine',
            'meta_key'			=> 'relilab_startdate',
            'orderby'			=> 'meta_value',
            'order'				=> 'ASC'
        ));
        global $post;
        ob_start();
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
                        load_template(dirname(__FILE__) . '/templates/single-termin-block.php',false);
                }
            }
            ?>
        </ul>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
    function enqueue_scripts() {
     //   wp_enqueue_script( 'custom-js', plugin_dir_url( __FILE__ ) . 'js/custom.js', array( 'jquery' ), '', true );
        wp_enqueue_style( 'single-termin-block-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
    }
    /**
     * @param string $date
     * @return string
     */
    static function getWochentag(string $date): string
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
    static function getMonat(string $date): string
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

}

$object = new RelilabTermine();
