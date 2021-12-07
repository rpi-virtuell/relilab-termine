<div class="wp-block-column relilab_termin_month">
    <h3><?php echo RelilabTermine::lastpostmonthcheck(RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate', true))) ?> </h3>
</div>


<div class="wp-block-columns relilab_termin_box has-background" style="display:flex; justify-content: space-around">
    <div class="wp-block-column" style=" flex: 0 0 175px; align-self: center">
        <div class="wp-block-group relilab_termin_day"
             onclick="location.href='<?php echo get_option('options_relilab_zoom_link') ?>'">
            <div style="margin: 5px 5px">
                <p class="has-text-align-center">zur Live Veranstaltung</p>
                <p class="has-text-align-center"><?php echo RelilabTermine::getWochentag(get_post_meta(get_the_ID(), 'relilab_startdate', true)); ?></p>
                <p class="has-text-align-center"><?php echo date('j', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true))) . '.'; ?></p>
                <p class="has-text-align-center"><?php echo RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate', true)) . ' ' . date('Y', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true))); ?></p>
                <p class="has-text-align-center"><?php echo date('H:i', strtotime(get_post_meta(get_the_ID(), 'relilab_startdate', true))) . ' - ' . date('H:i', strtotime(get_post_meta(get_the_ID(), 'relilab_enddate', true))) ?></p>
            </div>
        </div>
    </div>

    <div class="wp-block-column relilab_termin_content" style="flex: 1 0 300px">
        <h3 class="entry-title"><a class="has-text-align-center"
                                   href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo substr(get_the_excerpt(), 0, 150) . ' ...'; ?>  </p>
    </div>

    <div class="wp-block-column" style="flex: 0 1 200px">
        <?php if (has_post_thumbnail()) : ?>

            <div class="wp-block-image">
                <figure>
                    <?php the_post_thumbnail('medium'); ?>
                </figure>
            </div>
        <?php endif ?>

    </div>
</div>
