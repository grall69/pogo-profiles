<?php


function trainer_medals_func( $atts ) {
  global $wpdb;
  $sql = 'SELECT * FROM wp_type_medal ORDER BY medal_type ASC, id_medal ASC';
  $medals = $wpdb->get_results($sql);

  $count = 0;
  $content = "<div style='display: flex;flex-wrap: wrap;width: 100%;''>";

  for ($i = 0; $i != count($medals); $i++):
    if ($count == 6) {
      $count = 0;
      $content .= "<div style='display: flex;flex-wrap: wrap;width: 100%;''>";
    }

    if ($medals[$i]->medal_type == 1) {
      $event = get_medal_event_data($medals[$i]->medal_img, $medals[$i]->medal_data, $medals[$i]->medal_title);
      if ($event != "") {
        $content .= $event;
        $count = $count + 1;
      }
    } else {
      $content .= get_medal_data($medals[$i]->medal_img, $medals[$i]->medal_data, $medals[$i]->medal_bronze, $medals[$i]->medal_silver, $medals[$i]->medal_gold, $medals[$i]->medal_title);
      $count = $count + 1;
    }

    if ($count == 6) {
      $content .= "</div>";
    }

  endfor;

  if ($count != 6) {
    $content .= "</div>";
  }

  return $content;
}
add_shortcode( 'trainer_medals', 'trainer_medals_func' );

function get_medal_event_data( $medal, $data, $name ) {
  $uploads = wp_upload_dir();
  $upload_path = $uploads['baseurl'];

  ob_start(); ?>

  <?php
  global $wpdb;
  $id = um_profile_id();
  $sql = 'SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id =' . $id . " AND meta_key = '" . $data . "'";
  $messages = $wpdb->get_row($sql);

  if ($messages == null || $messages->meta_value == 'a:1:{i:0;s:2:"No";}') {
    return "";
  }
  ?>

  <div id="medal" class="bloc-medal">
    <div class="name-medal"><?php echo $name; ?></div>
    <img src="<?php echo $upload_path . "/ultimatemember/Medals/" . $medal . ".png"; ?>" width="148" height="148">
  </div>

  <?php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

function get_medal_data( $medal, $data, $bronze, $silver, $gold, $name ) {
  $uploads = wp_upload_dir();
  $upload_path = $uploads['baseurl'];

  ob_start(); ?>

  <?php
  global $wpdb;
  $id = um_profile_id();
  $sql = 'SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id =' . $id . " AND meta_key = '" . $data . "'";
  $messages = $wpdb->get_row($sql);?>

  <div id="medal" class="bloc-medal">
    <div class="name-medal"><?php echo $name; ?></div>
  <?php
  if (intval($messages->meta_value) < $bronze) { ?>
      <img src="<?php echo $upload_path . "/ultimatemember/Medals/" . $medal . "_shadow.png"; ?>" width="148" height="148">
    <?php
  } elseif (intval($messages->meta_value) < $silver) { ?>
      <img src="<?php echo $upload_path . "/ultimatemember/Medals/" . $medal . "_Bronze.png"; ?>" width="148" height="148">
    <?php
  } elseif (intval($messages->meta_value) < $gold) { ?>
      <img src="<?php echo $upload_path . "/ultimatemember/Medals/" . $medal . "_Silver.png"; ?>" width="148" height="148">
    <?php
  } else { ?>
      <img src="<?php echo $upload_path . "/ultimatemember/Medals/" . $medal . "_Gold.png"; ?>" width="148" height="148">
    <?php
  } ?>
    <div class="score-medal">
      <?php
        if ($messages->meta_value) {
          echo $messages->meta_value;
        } else {
          echo "0";
        }
      ?>
    </div>
  </div>

  <?php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}


function leaderboard_func( $atts ) {
  ob_start(); ?>

  <?php
  global $wpdb;
  $id = um_profile_id();
  $sql = "SELECT wp_users.user_login AS 'user', CAST(xp.meta_value AS UNSIGNED) AS 'xp', country.meta_value AS 'country', team.meta_value AS 'team', name.meta_value AS 'pseudo'
          FROM wp_users
          LEFT JOIN wp_usermeta xp ON wp_users.ID = xp.user_id
          LEFT JOIN wp_usermeta country ON wp_users.ID = country.user_id
          LEFT JOIN wp_usermeta team ON wp_users.ID = team.user_id
          LEFT JOIN wp_usermeta name ON wp_users.ID = name.user_id
          WHERE
            xp.meta_key = 'trainer_xp' AND
            country.meta_key = 'country' AND
            team.meta_key = 'trainer_team' AND
            name.meta_key = 'trainer_name'
          ORDER BY xp.meta_value DESC";
  $messages = $wpdb->get_results($sql);
  $upload_dir = wp_upload_dir();

  for ($i = 0; $i != count($messages); $i++):?>
    <div class="rows">
      <div class="cells" data-title="Rank">
        <?php echo $i + 1; ?>
      </div>
      <div class="cells" data-title="IGN">
        <?php echo $messages[$i]->pseudo; ?>
      </div>
      <div class="cells" data-title="Country">
        <?php echo $messages[$i]->country; ?>
      </div>
      <div class="cells" data-title="Team">
        <?php
        echo '<img src="' . $upload_dir['baseurl'] . '/ultimatemember/Team/' . $messages[$i]->team . '/profile_photo.png" width="25" height="25" alt="">';
          /*if ($messages[$i]->team == 'Valor') {
            echo '<img src="' . $ultimatemember->files->upload_basedir . 'Team/' . $messages[$i]->team . '/profile_photo.png" width="25" height="25" alt="">';
          } elseif ($messages[$i]->team == 'Valor') {
            echo "LVL : 2";
          } else {

          }*/
        ?>
      </div>
      <div class="cells" data-title="XP Total">
        <?php echo $messages[$i]->xp; ?>
      </div>
    </div>
  <?php endfor; ?>
  <?php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}
add_shortcode( 'leaderboard', 'leaderboard_func' );


function trainer_country_func( $atts ) {
  ob_start(); ?>

  <?php
  global $wpdb;
  $id = um_profile_id();
  $sql = 'SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id =' . $id . " AND meta_key = 'country'";
  $messages = $wpdb->get_row($sql); ?>

  <div style="float:left;width:100%;text-align: center;font-weight: 600;font-size: xx-large;padding-bottom: 20px;">
    <?php
      echo "Country : " . $messages->meta_value;
    ?>
  </div>

  <?php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}
add_shortcode( 'trainer_country', 'trainer_country_func' );

function trainer_xp_lvl_func( $atts ) {
  ob_start(); ?>

  <?php
  global $wpdb;
  $id = um_profile_id();
  $sql = 'SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id =' . $id . " AND meta_key = 'trainer_xp'";
  $messages = $wpdb->get_row($sql); ?>

  <div style="float:left;width:100%;text-align: center;font-weight: 600;font-size: xx-large;padding-bottom: 20px;">
    <?php
      if (intval($messages->meta_value) < 1000) {
        echo "LVL : 1";
      } elseif (intval($messages->meta_value) < 3000) {
        echo "LVL : 2";
      } elseif (intval($messages->meta_value) < 6000) {
        echo "LVL : 3";
      } elseif (intval($messages->meta_value) < 10000) {
        echo "LVL : 4";
      } elseif (intval($messages->meta_value) < 15000) {
        echo "LVL : 5";
      } elseif (intval($messages->meta_value) < 21000) {
        echo "LVL : 6";
      } elseif (intval($messages->meta_value) < 28000) {
        echo "LVL : 7";
      } elseif (intval($messages->meta_value) < 36000) {
        echo "LVL : 8";
      } elseif (intval($messages->meta_value) < 45000) {
        echo "LVL : 9";
      } elseif (intval($messages->meta_value) < 55000) {
        echo "LVL : 10";
      } elseif (intval($messages->meta_value) < 65000) {
        echo "LVL : 11";
      } elseif (intval($messages->meta_value) < 75000) {
        echo "LVL : 12";
      } elseif (intval($messages->meta_value) < 85000) {
        echo "LVL : 13";
      } elseif (intval($messages->meta_value) < 100000) {
        echo "LVL : 14";
      } elseif (intval($messages->meta_value) < 120000) {
        echo "LVL : 15";
      } elseif (intval($messages->meta_value) < 140000) {
        echo "LVL : 16";
      } elseif (intval($messages->meta_value) < 160000) {
        echo "LVL : 17";
      } elseif (intval($messages->meta_value) < 185000) {
        echo "LVL : 18";
      } elseif (intval($messages->meta_value) < 210000) {
        echo "LVL : 19";
      } elseif (intval($messages->meta_value) < 260000) {
        echo "LVL : 20";
      } elseif (intval($messages->meta_value) < 335000) {
        echo "LVL : 21";
      } elseif (intval($messages->meta_value) < 435000) {
        echo "LVL : 22";
      } elseif (intval($messages->meta_value) < 560000) {
        echo "LVL : 23";
      } elseif (intval($messages->meta_value) < 710000) {
        echo "LVL : 24";
      } elseif (intval($messages->meta_value) < 900000) {
        echo "LVL : 25";
      } elseif (intval($messages->meta_value) < 1100000) {
        echo "LVL : 26";
      } elseif (intval($messages->meta_value) < 1350000) {
        echo "LVL : 27";
      } elseif (intval($messages->meta_value) < 1650000) {
        echo "LVL : 28";
      } elseif (intval($messages->meta_value) < 2000000) {
        echo "LVL : 29";
      } elseif (intval($messages->meta_value) < 2500000) {
        echo "LVL : 30";
      } elseif (intval($messages->meta_value) < 3000000) {
        echo "LVL : 31";
      } elseif (intval($messages->meta_value) < 3750000) {
        echo "LVL : 32";
      } elseif (intval($messages->meta_value) < 4750000) {
        echo "LVL : 33";
      } elseif (intval($messages->meta_value) < 6000000) {
        echo "LVL : 34";
      } elseif (intval($messages->meta_value) < 7500000) {
        echo "LVL : 35";
      } elseif (intval($messages->meta_value) < 9500000) {
        echo "LVL : 36";
      } elseif (intval($messages->meta_value) < 12000000) {
        echo "LVL : 37";
      } elseif (intval($messages->meta_value) < 15000000) {
        echo "LVL : 38";
      } elseif (intval($messages->meta_value) < 20000000) {
        echo "LVL : 39";
      } else {
        echo "LVL : 40";
      }
      ?>
  </div>
  <div style="float:left;width:100%;text-align: center;font-weight: 600;font-size: xx-large;padding-bottom: 30px;">
    <?php
    if ($messages->meta_value) {
      echo "TOTAL XP : " . $messages->meta_value;
    } else {
      echo "TOTAL XP : 0";
    }
    ?>
  </div>

  <?php
  $content = ob_get_contents();
  ob_end_clean();
	return $content;
}
add_shortcode( 'trainer_xp_lvl', 'trainer_xp_lvl_func' );











add_filter('show_inactive_plugin_infos', "__return_false");

function one_page_express_get_post_thumbnail()
{
    // $thumbnail = get_the_post_thumbnail();
    ob_start();
    the_post_thumbnail('post-thumbnail', array('class' => 'blog-postimg'));
    $thumbnail = trim(ob_get_clean());

    if (empty($thumbnail)) {
        if (is_customize_preview() || 1) {
            return "<img src='https://placeholdit.imgix.net/~text?txtsize=38&bg=FF7F66&txtclr=FFFFFFe&w=400&h=250' class='blog-postimg'/>";
        } else {
            return $thumbnail;
        }
    }

    return $thumbnail;
}

function one_page_express_latest_news_excerpt_length()
{
    return 30;
}

function one_page_express_latest_excerpt_more()
{
    return "[&hellip;]";
}

function one_page_express_latest_news()
{
    ob_start(); ?>
    <?php
    $recentPosts = new WP_Query();
    $cols        = intval(\OnePageExpress\Companion::getThemeMod('one_page_express_latest_news_columns', 4));

    $post_numbers = 12 / $cols;

    add_filter('excerpt_length', 'one_page_express_latest_news_excerpt_length');
    add_filter('excerpt_more', 'one_page_express_latest_excerpt_more');
    $recentPosts->query('showposts=' . $post_numbers . ';post_status=publish;post_status=publish;post_type=post');
    while ($recentPosts->have_posts()):
        $recentPosts->the_post(); ?>
        <div id="post-<?php the_ID(); ?>" class="blog-postcol cp<?php echo $cols; ?>cols">
            <div class="post-content">
                <?php if (has_post_thumbnail()): ?>
                    <a class="post-list-item-thumb" href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail(); ?>
                    </a>
                <?php endif; ?>
                <div class="row_345">
                    <h3 class="blog-title">
                        <a href="<?php the_permalink(); ?>" rel="bookmark">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    <?php the_excerpt(); ?>
                    <a class="button blue small" href="<?php echo get_permalink(); ?>">
                        <span data-theme="one_page_express_latest_news_read_more"><?php \OnePageExpress\Companion::echoMod('one_page_express_latest_news_read_more', 'Read more'); ?></span>
                    </a>
                    <?php get_template_part('template-parts/content-post-footer'); ?>
                </div>
            </div>
        </div>
        <?php
    endwhile;
    wp_reset_postdata();

    remove_filter('excerpt_length', 'one_page_express_latest_news_excerpt_length');
    remove_filter('excerpt_more', 'one_page_express_latest_excerpt_more');
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

function one_page_express_news_static()
{
    return one_page_express_latest_news();
}

add_shortcode('one_page_express_latest_news', 'one_page_express_latest_news');

function one_page_express_blog_link()
{
    if ('page' == get_option('show_on_front')) {
        if (get_option('page_for_posts')) {
            return esc_url(get_permalink(get_option('page_for_posts')));
        } else {
            return esc_url(home_url('/?post_type=post'));
        }
    } else {
        return esc_url(home_url('/'));
    }
}

add_shortcode('one_page_express_blog_link', 'one_page_express_blog_link');

function one_page_express_contact_form($attrs = array())
{
    $atts = shortcode_atts(
        array(
            'shortcode' => "",
        ),
        $attrs
    );
    // compatibility with free //
    $contact_shortcode = get_theme_mod('one_page_express_contact_form_shortcode', '');
    if ($atts['shortcode']) {
        $contact_shortcode = "[" . html_entity_decode(html_entity_decode($atts['shortcode'])) . "]";
    }
    ob_start();
    if ($contact_shortcode !== "") {
        echo do_shortcode($contact_shortcode);
    } else {
        echo '<p style="text-align:center;color:#ababab">' . __('Contact form will be displayed here. To activate it you have to click this area and set the shortcode parameter in Customizer.',
                'one_page_express-companion') . '</p>';
    }

    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

add_shortcode('one_page_express_contact_form', 'one_page_express_contact_form');

add_filter('cloudpress\template\page_content',
    function ($content) {
        $content = str_replace('[one_page_express_latest_news]', one_page_express_latest_news(), $content);
        $content = str_replace('[one_page_express_contact_form]', one_page_express_contact_form(), $content);
        $content = str_replace('[one_page_express_blog_link]', one_page_express_blog_link(), $content);
        $content = str_replace('[tag_companion_uri]', \OnePageExpress\Companion::instance()->themeDataURL(), $content);

        return $content;
    });


add_filter('cloudpress\companion\cp_data',
    function ($data, $companion) {

        $sectionsJSON             = $companion->themeDataPath("/sections/sections.json");
        $contentSections          = json_decode(file_get_contents($sectionsJSON), true);
        $data['data']['sections'] = $contentSections;

        $showPro = apply_filters('ope_show_info_pro_messages', true);

        if ($showPro) {
            $proSectionsJSON = $companion->themeDataPath("/sections/pro-only-sections.json");
            if (file_exists($proSectionsJSON)) {
                $proSections              = json_decode(file_get_contents($proSectionsJSON), true);
                $data['data']['sections'] = array_merge($contentSections, $proSections);
            }
        }

        return $data;
    }, 10, 2);

add_action('cloudpress\template\load_assets',
    function ($companion) {
        $ver = $companion->version;
        wp_enqueue_style($companion->getThemeSlug() . '-common-css', $companion->themeDataURL('/templates/css/common.css'), array($companion->getThemeSlug() . '-style'), $ver);
        wp_enqueue_style('companion-page-css', $companion->themeDataURL('/sections/content.css'), array(), $ver);
        wp_enqueue_style('companion-cotent-swap-css', $companion->themeDataURL('/templates/css/HoverFX.css'), array(), $ver);

        wp_enqueue_script('companion-lib-hammer', $companion->themeDataURL('/templates/js/libs/hammer.js'), array(), $ver);
        wp_enqueue_script('companion-lib-modernizr', $companion->themeDataURL('/templates/js/libs/modernizr.js'), array(), $ver);
        wp_register_script('companion-' . $companion->getThemeSlug(), null, array('jquery', 'companion-lib-hammer', 'companion-lib-modernizr'), $ver);

        if ( ! is_customize_preview()) {
            wp_enqueue_script('companion-cotent-swap', $companion->themeDataURL('/templates/js/HoverFX.js'), array('companion-' . $companion->getThemeSlug()), $ver);
        }
        wp_enqueue_script('companion-scripts', $companion->themeDataURL('/sections/scripts.js'), array('companion-' . $companion->getThemeSlug()), $ver);
    });

add_action('cloudpress\customizer\preview_scripts',
    function ($customizer) {
        $ver = $customizer->companion()->version;
        wp_enqueue_script(
            $customizer->companion()->getThemeSlug() . "_preview-handle", $customizer->companion()->themeDataURL() . "/preview-handles.js", array('cp-customizer-preview'), $ver
        );
    });


add_action('cloudpress\customizer\global_scripts',
    function ($customizer) {
        $ver = $customizer->companion()->version;
        wp_enqueue_script(
            $customizer->companion()->getThemeSlug() . "_companion_theme_customizer",
            $customizer->companion()->themeDataURL() . "/customizer.js",
            array('cp-customizer-base'),
            $ver,
            true
        );
    });

function one_page_header_css()
{
    $headerContentCSS = \OnePageExpress\Companion::getThemeMod(
        'onepage_builder_header_content_css', array()
    );

    $headerContentCSS = array_merge(array(
        'title-margin-top'       => 'inherit',
        'title-margin-bottom'    => 'inherit',
        'title-text-align'       => 'right',
        'subtitle-margin-top'    => 'inherit',
        'subtitle-margin-bottom' => 'inherit',
        'subtitle-text-align'    => 'right',
        'buttons-position'       => "right",
    ), $headerContentCSS);

    $mappedSettings  = array();
    $buttonsAlignCss = array();
    switch ($headerContentCSS['buttons-position']) {
        case "left":
            $buttonsAlignCss = array(
                "text-align:left",
            );
            break;
        case "center":
            $buttonsAlignCss = array(
                "text-align:center",
            );
            break;
        case "right":
            $buttonsAlignCss = array(
                "text-align:right",
            );
            break;
    }

    foreach ($headerContentCSS as $key => $value) {
        $contentEL = "";
        if (strpos($key, "title-") === 0) {
            $key       = str_replace('title-', '', $key);
            $contentEL = "title";
        } else {
            $key       = str_replace('subtitle-', '', $key);
            $contentEL = "subtitle";
        }

        if ( ! isset($mappedSettings[$contentEL])) {
            $mappedSettings[$contentEL] = array();
        }

        $mappedSettings[$contentEL][$key] = $value;
    } ?>
    <style>
        .header-description-right {
        <?php echo implode(";", $buttonsAlignCss); ?>
        }

        .header-description-right h1 {
            margin-top: <?php echo one_page_builder_get_css_value($mappedSettings['title']['margin-top'], "em"); ?>;
            margin-bottom: <?php echo one_page_builder_get_css_value($mappedSettings['title']['margin-bottom'], "em"); ?>;
            text-align: <?php echo $mappedSettings['title']['text-align']; ?>;
            margin-left: <?php echo $mappedSettings['title']['text-align'] === "right" ? "5%" : "0%"; ?>;
            margin-right: <?php echo $mappedSettings['title']['text-align'] === "left" ? "5%" : "0%"; ?>
        }

        .header-description-right .header-subtitle {
            margin-top: <?php echo one_page_builder_get_css_value($mappedSettings['subtitle']['margin-top'], "em"); ?>;
            margin-bottom: <?php echo one_page_builder_get_css_value($mappedSettings['subtitle']['margin-bottom'], "em"); ?>;
            text-align: <?php echo $mappedSettings['subtitle']['text-align']; ?>;
            margin-left: <?php echo $mappedSettings['subtitle']['text-align'] === "right" ? "5%" : "0%"; ?>;
            margin-right: <?php echo $mappedSettings['subtitle']['text-align'] === "left" ? "5%" : "0%"; ?>
        }
    </style>
    <?php

}

function one_page_builder_get_css_value($value, $unit = false)
{
    $noUnitValues = array('inherit', 'auto', 'initial');
    if ( ! in_array($value, $noUnitValues)) {
        return $value . $unit;
    }

    return $value;
}

function one_page_inner_header_css()
{
    $headerContentCSS = \OnePageExpress\Companion::getThemeMod(
        'onepage_builder_inner_header_content_css', array()
    );

    $headerContentCSS = array_merge(array(
        'title-margin-top'       => 'inherit',
        'title-margin-bottom'    => 'inherit',
        'title-text-align'       => 'right',
        'subtitle-margin-top'    => 'inherit',
        'subtitle-margin-bottom' => 'inherit',
        'subtitle-text-align'    => 'right',
        'buttons-position'       => "right",
    ), $headerContentCSS);

    $mappedSettings = array();

    $contentAlignCss = array();

    switch ($headerContentCSS['buttons-position']) {
        case "left":
            $contentAlignCss = array(
                "text-align:left",
                "margin-left:0px",
                "float:none",
                "width:50%",
            );
            break;
        case "center":
            $contentAlignCss = array(
                "text-align:center",
                "margin-left:auto",
                "margin-right:auto",
                "float:none",
                "width:80%",
            );
            break;
        case "right":
            $contentAlignCss = array(
                "text-align:right",
                "margin-left:50%",
                "margin-right:auto",
                "float:none",
                "width:50%",
            );
            break;
    }

    foreach ($headerContentCSS as $key => $value) {
        $contentEL = "";
        if (strpos($key, "title-") === 0) {
            $key       = str_replace('title-', '', $key);
            $contentEL = "title";
        } else {
            $key       = str_replace('subtitle-', '', $key);
            $contentEL = "subtitle";
        }

        if ( ! isset($mappedSettings[$contentEL])) {
            $mappedSettings[$contentEL] = array();
        }

        $mappedSettings[$contentEL][$key] = $value;
    } ?>
    <style>
        @media only screen and (min-width: 768px) {
            .header-description-right h1 {
                margin-top: <?php echo one_page_builder_get_css_value($mappedSettings['title']['margin-top'], "em"); ?>;
                margin-bottom: <?php echo one_page_builder_get_css_value($mappedSettings['title']['margin-bottom'], "em"); ?>;
                text-align: <?php echo $mappedSettings['title']['text-align']; ?>;
            }

            .header-description-right .header-subtitle {
                margin-top: <?php echo one_page_builder_get_css_value($mappedSettings['subtitle']['margin-top'], "em"); ?>;
                margin-bottom: <?php echo one_page_builder_get_css_value($mappedSettings['subtitle']['margin-bottom'], "em"); ?>;
                text-align: <?php echo $mappedSettings['subtitle']['text-align']; ?>;
            }

            .header-description-right {
            <?php echo implode(";", $contentAlignCss); ?>
            }
        }
    </style>
    <?php

}

add_action('cloudpress\companion\activated\one-page-express', function ($companion) {
    $companion->__createFrontPage();
});

add_action('cloudpress\companion\deactivated\one-page-express', function ($companion) {
    $companion->restoreFrontPage();
});

function one_page_express_get_front_page_content($companion)
{
    $defaultSections = array("stripped-coloured-icon-boxes", "about-big-images-section", "content-image-left", "content-image-right", "portfolio-full-section", "testimonials-boxed-section", "cta-blue-section", "team-colors-section", "numbers-section", "blog-section", "contact-section");

    $alreadyColoredSections = array("numbers-section", "contact-section", "cta-blue-section");

    $availableSections = $companion->loadJSON($companion->themeDataPath("/sections/sections.json"));

    $content = "";

    $colors     = array('#ffffff', '#f6f6f6');
    $colorIndex = 0;

    foreach ($defaultSections as $ds) {
        foreach ($availableSections as $as) {
            if ($as['id'] == $ds) {
                $_content = $as['content'];

                if (strpos($_content, 'data-bg="transparent"') === false && ! in_array($ds, $alreadyColoredSections)) {
                    $_content   = preg_replace('/\<div/', '<div style="background-color:' . $colors[$colorIndex] . '" ', $_content, 1);
                    $colorIndex = $colorIndex ? 0 : 1;
                } else {
                    $colorIndex = 0;
                }

                $_content = preg_replace('/\<div/', '<div id="' . $as['elementId'] . '" ', $_content, 1);

                $content .= $_content;
                break;
            }
        }
    }

    return $content;
}

add_filter('cloudpress\companion\front_page_content',
    function ($content, $companion) {
        $content = one_page_express_get_front_page_content($companion);

        return \OnePageExpress\Companion::filterDefault($content);
    }, 10, 2);

add_filter('cloudpress\companion\template',
    function ($template, $companion, $post) {

        if ( ! $post) {
            return $template;
        }

        if ($companion->isActiveThemeSupported()) {
            if ($companion->isFrontPage($post->ID)) {
                if (strpos($template, "front-page.php") !== false) {
                    return $template;
                } else {
                    $template = $companion->themeDataPath("/templates/home-page.php");
                    add_filter('body_class', 'one_page_express_homepage_class');
                }
            } else {
                if ($companion->isMaintainable($post->ID)) {
                    add_filter('body_class', 'one_page_express_maintaibale_class');
                }
            }

        }

        return $template;
    }, 10, 3);


function one_page_express_homepage_class($classes)
{

    $classes[] = "homepage-template";

    foreach ($classes as $index => $class) {
        switch ($class) {
            case "page-template-default":
            case "page":
                unset($classes[$index]);
                break;
        }

    }

    return $classes;
}

function one_page_express_maintaibale_class($classes)
{

    $classes[] = "ope-maintainable";

    return $classes;
}

add_filter('cloudpress\customizer\control\content_sections\data',
    function ($data) {
        $categories = array(
            'overlapable',
            'about',
            'features',
            'content',
            'cta',
            'protfolio',
            'testimonials',
            'numbers',
            'clients',
            'team',
            'latest_news',
            'contact',
        );

        $result = array();

        foreach ($categories as $cat) {
            if (isset($data[$cat])) {
                $result[$cat] = $data[$cat];
                unset($data[$cat]);
            }
        }

        $result = array_merge($result, $data);

        return $result;
    });

add_filter('cloudpress\customizer\control\content_sections\category_label',
    function ($label, $category) {

        switch ($category) {
            case 'latest_news':
                $label = __("Latest News", 'cloudpress_companion');
                break;

            case 'cta':
                $label = __("Call to action", 'cloudpress_companion');
                break;

            default:
                $label = __($label, 'cloudpress_companion');
                break;
        }

        return $label;
    }, 10, 2);

add_action('wp_head', function () {
    $margin      = get_theme_mod('one_page_express_front_page_header_margin', '230px');
    $overlap_mod = get_theme_mod('one_page_express_front_page_header_overlap', true);
    if (1 == intval($overlap_mod)): ?>
        <style data-name="overlap">
            @media only screen and (min-width: 768px) {
                .header-homepage {
                    padding-bottom: <?php echo  $margin; ?>;
                }

                .homepage-template .content {
                    position: relative;
                    z-index: 10;
                }

                .homepage-template .page-content div[data-overlap]:first-of-type > div:first-of-type {
                    margin-top: -<?php echo  $margin; ?>;
                    background: transparent !important;
                }
            }
        </style>
        <?php
    endif;
});


add_action('edit_form_after_title', 'one_page_express_add_maintainable_filter');

function one_page_express_add_maintainable_filter($post)
{
    $companion    = \OnePageExpress\Companion::instance();
    $maintainable = $companion->isMaintainable($post->ID);

    add_editor_style(get_template_directory_uri() . "/style.css");
    add_editor_style(get_stylesheet_uri());

    add_editor_style($companion->themeDataURL('/templates/css/common.css'));
    add_editor_style($companion->themeDataURL('/sections/content.css'));
    add_editor_style($companion->themeDataURL('/templates/css/HoverFX.css'));
    add_editor_style(get_template_directory_uri() . '/assets/font-awesome/font-awesome.min.css');


    if ($maintainable) {
        add_filter('tiny_mce_before_init', 'one_page_express_maintainable_pages_tinymce_init');
    }
}


function one_page_express_maintainable_pages_tinymce_init($init)
{
    $init['verify_html'] = false;

    // convert newline characters to BR
    $init['convert_newlines_to_brs'] = true;

    // don't remove redundant BR
    $init['remove_redundant_brs'] = false;


    $opts                            = '*[*]';
    $init['valid_elements']          = $opts;
    $init['extended_valid_elements'] = $opts;
    $init['forced_root_block']       = false;
    $init['paste_as_text']           = true;

    return $init;
}


function one_page_express_remove_page_attribute_support($post)
{
    $companion = \OnePageExpress\Companion::instance();
    if ($post && $companion->isFrontPage($post->ID)) {
        remove_meta_box('pageparentdiv', 'page', 'side');

    }
}

add_action('edit_form_after_editor', 'one_page_express_remove_page_attribute_support');


add_filter('one_page_express_header_presets', 'one_page_express_header_presets_pro_info');

function one_page_express_header_presets_pro_info($presets)
{


    if (apply_filters('ope_show_info_pro_messages', true)) {
        $companion = \OnePageExpress\Companion::instance();

        $proPresets = $companion->themeDataPath("/pro-only-presets.php");
        if (file_exists($proPresets)) {
            $proPresets = require_once($proPresets);
        } else {
            $proPresets = array();
        }

        $presets = array_merge($presets, $proPresets);

    }

    return $presets;
}



// discount notice

function one_page_express_discount_end_date() {
    return "2017-12-02";
}

function one_page_express_discount_link(){
    return esc_url("http://onepageexpress.com/#pricing");
}

function one_page_express_discount_notice_script()
{
    ?>
    <script type="text/javascript" >
        (function ($) {
            jQuery(document).on( 'click', '.ope-discount-notice .notice-dismiss', function() {
                jQuery.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'one_page_express_discount_notice_dismiss'
                    }
                });
            })
        })(jQuery);
    </script>
    <?php
}

add_action("wp_ajax_one_page_express_discount_notice_dismiss", function() {
    update_option( 'one-page-express-'.one_page_express_discount_end_date().'-notice-dismissed', 1);
});

function one_page_express_discount_notice() {
    if (get_option( 'one-page-express-'.one_page_express_discount_end_date().'-notice-dismissed', 0)) {
        return;
    }
    ?>
    <div class="ope-discount-notice notice notice-info is-dismissible" style="background-color: #fdffb3">
        <p style="font-size: 20px;">
            Black Friday Special Offer - <span style="color:red">40% discount</span> for One Page Express PRO

            <a class="button button-primary" style="margin-left:10px;float: right;" target="_blank" href="http://onepageexpress.com/#features-6">See PRO Features</a>
            <a class="button" style="background-color: red;border-color: #d65600;color: #ffffff;float: right;" target="_blank" href="<?php echo one_page_express_discount_link(); ?>">Get the offer</a>
        </p>
    </div>
    <?php
}



add_action("admin_init", function() {
    $show = apply_filters('ope_show_info_pro_messages', true);
    if ($show && new DateTime() < new DateTime(one_page_express_discount_end_date())) {
        add_action( 'admin_notices', 'one_page_express_discount_notice' );
        add_action( 'admin_footer', 'one_page_express_discount_notice_script' );


        add_action('cloudpress\customizer\global_scripts',
        function ($customizer) {
            $ver = $customizer->companion()->version;
            wp_localize_script($customizer->companion()->getThemeSlug() . "_companion_theme_customizer", "ope_discount", array(
                "buylink" => one_page_express_discount_link(),
                "msg" => "Get PRO - 40% Black Friday Discount"
            ));
        });
    }
});
