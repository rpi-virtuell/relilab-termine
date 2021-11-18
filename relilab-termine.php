<?php
include  'relilab-termine-ics.php';
/**
 *Plugin Name: relilab Termine
 */

class RelilabTermine{

    private static string $lastPostMonth = '';

    public function __construct(){
        add_shortcode('relilab_termine',array($this,'termineAusgeben'));
        add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts'));
        add_action('init','ical');
    }
    function   termineAusgeben( $atts ){

        $posts = array(
            'post_type'			=> 'post',
            'posts_per_page'	=> -1,
            'category'          => 'termine',
            'meta_key'			=> 'relilab_startdate',
            'meta_value'        =>  false,
            'meta_compare'      =>  '!=',
            'orderby'			=> 'meta_value',
            'order'				=> 'ASC',
        );
        //TODO: WIP fetching only specific category data doesn't work
        if(isset($_GET['cat'])  && $_GET['cat'] == 'relilab-Talks'){
            $posts['category'] = 'relilab-Talks';
            $posts=get_posts($posts);
        }
        elseif(isset($_GET['cat'])  && $_GET['cat'] == 'relilab-CAFÉ'){
            $posts['category'] = 'relilab-CAFÉ';
            $posts=get_posts($posts);
        }
        else
            $posts=get_posts($posts);

    ?>
        <div class="wp-block-column" >
            <a class="has-text-align-center" href="<?php echo 'https://test.rpi-virtuell.de/termine/?cat=relilab-Talks'; ?>"><?php echo 'relilab-Talks' ?></a>
        </div>
        <div class="wp-block-column" >
            <a class="has-text-align-center" href="<?php echo 'https://test.rpi-virtuell.de/termine/?cat=relilab-CAFÉ'; ?>"><?php echo 'relilab-CAFÉ' ?></a>
        </div>
    <?php

global $post;
        ob_start();
        ?>
        <ul>
            <?php
            foreach ($posts as $post) {
                setup_postdata( $post );
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
    static function getWochentag(string $date): string{
        $wochentag = array(
            'Mon' => 'Montag',
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
    static function getMonat(string $date): string{
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
            'Oct'   => 'Oktober',
            'Nov'   => 'November',
            'Dec'   => 'Dezember',
        );
        if(!empty($date))
            return $monat[date('M',strtotime($date))];
        else
            return '';
    }
    static function lastpostmonthcheck(string $currenPostMonth){
        if(self::$lastPostMonth == $currenPostMonth)
            $currenPostMonth = '';
        else
            self::$lastPostMonth = $currenPostMonth;
    return $currenPostMonth;
    }

}

$object = new RelilabTermine();
