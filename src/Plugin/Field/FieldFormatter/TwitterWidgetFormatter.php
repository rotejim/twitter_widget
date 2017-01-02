<?php

/**
 * @file
 * Contains \Drupal\twitter_widget\Plugin\Field\FieldFormatter\TwitterFeedFormatter.
 */

namespace Drupal\twitter_widget\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Plugin implementation of the 'twitter_widget' formatter.
 *
 * @FieldFormatter(
 *   id = "twitter_widget",
 *   label = @Translation("Twitter Widget"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TwitterWidgetFormatter extends FormatterBase
{

    /**
     * {@inheritdoc}
     */
    public static function defaultSettings()
    {
        return array(
            "widget_type" => "p",
            "twitter_user" => "",
            "iframe_height" => "500",
            "iframe_css_class" => "",

            "collection_id" => "",
            "collection_structure" => "",
            "collection_cant" => "",

            "profile_structure" => "",
            "show_screen_name" => "0",
            "tweet_text" => "0",
            "show_followers" => "0",

            "list" => "",

            "button_size" => "0",
            "theme_background" => "",
            "link_color" => "",

        ) + parent::defaultSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(array $form, FormStateInterface $form_state)
    {
        $elements = parent::settingsForm($form, $form_state);
        $elements['widget_type'] = array(
            '#type' => 'select',
            '#title' => $this->t('Widget type'),
            '#description' => $this->t('Choose a widget type for embedded'),
            '#options' => array(
                'p' => $this->t('Profile'),
                'c' => $this->t('Collection'),
                'l' => $this->t('List'),
                'k' => $this->t('Likes'),
                'h' => $this->t('Hashtag Button'),
            ),
            '#default_value' => $this->getSetting('widget_type'),
        );
        /* PROFILE FORMAT ****************************************************************/
        $elements['profile_structure'] = array(
            '#title' => t('Structure'),
            '#type' => 'select',
            '#options' => array(
                'follow' => $this->t('Follow Button'),
                'mention' => $this->t('Mention Button'),
                'list' => $this->t('List')
            ),
            '#states' => [
                'visible' => [
                    ':input[name$="[widget_type]"]' => ['value' => 'p'],
                ],
                'required' => array(
                    ':input[name$="[widget_type]"]' => ['value' => 'p']
                ),
            ],
            '#default_value' => $this->getSetting('profile_structure'),
        );
        $elements['show_screen_name'] = array(
            '#type' => 'checkbox',
            '#title' => $this->t('Hide Username'),
            '#description' => $this->t('Would you like to simplify the button text?'),
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'follow'],
                        ':input[name$="[widget_type]"]' => ['value' => 'p']
                    ]
                ]
            ],
            '#default_value' => $this->getSetting('button_size'),
        );
        $elements['tweet_text'] = array(
            '#title' => t('Tweet text'),
            '#type' => 'textfield',
            '#description' => $this->t('Text in the tweet'),
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'mention'],
                        ':input[name$="[widget_type]"]' => ['value' => 'p']
                    ],
                    [
                        ':input[name$="[widget_type]"]' => ['value' => 'h']
                    ],
                ]
            ],
        );
        /* COLLECTION FORMAT *************************************************************/
        $elements['collection_id'] = array(
            '#title' => t('Collection ID'),
            '#type' => 'textfield',
            '#maxlength' => 18,
            '#minlength' => 18,
            '#attributes' => array(
                'data-type' => 'number',
            ),
            '#description' => $this->t('Long number example: 539487832448843776'),
            '#states' => [
                'visible' => [
                    ':input[name$="[widget_type]"]' => ['value' => 'c'],
                ],
                'required' => array(
                    ':input[name$="[widget_type]"]' => ['value' => 'c']
                ),
            ],
            '#default_value' => $this->getSetting('collection_id'),
        );
        $elements['collection_structure'] = array(
            '#title' => t('Structure'),
            '#type' => 'select',
            '#options' => array(
                'grid' => $this->t('Grid'),
                'list' => $this->t('List')
            ),
            '#states' => [
                'visible' => [
                    ':input[name$="[widget_type]"]' => ['value' => 'c'],
                ],
                'required' => array(
                    ':input[name$="[widget_type]"]' => ['value' => 'c']
                ),
            ],
            '#default_value' => $this->getSetting('collection_structure'),
        );

        $elements['collection_cant'] = array(
            '#type' => 'number',
            '#title' => $this->t('Number of twetts to show'),
            '#default_value' => $this->getSetting('collection_cant'),
            '#min' => 1,
            '#max' => 6,
            '#step' => 1,
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[collection_structure]"]' => ['value' => 'grid'],
                        ':input[name$="[widget_type]"]' => ['value' => 'c']
                    ]
                ],
                'required' => array(
                    [
                        ':input[name$="[collection_structure]"]' => ['value' => 'grid'],
                        ':input[name$="[widget_type]"]' => ['value' => 'c']
                    ]
                ),
            ],
        );

        /* LIST FORMAT */
        $elements['list'] = array(
            '#title' => t('List name'),
            '#type' => 'textfield',
            '#description' => $this->t('List name example: national-parks'),
            '#states' => [
                'visible' => [
                    ':input[name$="[widget_type]"]' => ['value' => 'l'],
                ],
                'required' => array(
                    ':input[name$="[widget_type]"]' => ['value' => 'l']
                ),
            ],
        );
        /* COMMON FORMATS */
        $elements['link_color'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Links color'),
            '#description' => $this->t('Hexadecimal color format example: E81C4F'),
            '#default_value' => $this->getSetting('link_color'),
            '#maxlength' => 6,
            '#minlength' => 6,
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[collection_structure]"]' => ['value' => 'list'],
                        ':input[name$="[widget_type]"]' => ['value' => 'c']
                    ],
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'list'],
                        ':input[name$="[widget_type]"]' => ['value' => 'p']
                    ],
                    [':input[name$="[widget_type]"]' => ['value' => 'l']],
                    [':input[name$="[widget_type]"]' => ['value' => 'k']],
                ],
                'required' => array(
                    ':input[name$="[collection_structure]"]' => ['value' => 'list'],
                    ':input[name$="[profile_structure]"]' => ['value' => 'list'],
                    [':input[name$="[widget_type]"]' => ['value' => 'l']],
                    [':input[name$="[widget_type]"]' => ['value' => 'k']],
                ),
            ],
        );
        $elements['theme_background'] = array(
            '#title' => t('Theme background'),
            '#type' => 'select',
            '#options' => array(
                'light' => $this->t('Light'),
                'dark' => $this->t('Dark')
            ),
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[collection_structure]"]' => ['value' => 'list'],
                        ':input[name$="[widget_type]"]' => ['value' => 'c']
                    ],
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'list'],
                        ':input[name$="[widget_type]"]' => ['value' => 'p']
                    ],
                    [':input[name$="[widget_type]"]' => array('value' => 'l')],
                    [':input[name$="[widget_type]"]' => array('value' => 'k')],
                ],
                'required' => array(
                    [
                        ':input[name$="[collection_structure]"]' => ['value' => 'list'],
                        ':input[name$="[widget_type]"]' => ['value' => 'c']
                    ],
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'list'],
                        ':input[name$="[widget_type]"]' => ['value' => 'p']
                    ],
                    [':input[name$="[widget_type]"]' => array('value' => 'l')],
                    [':input[name$="[widget_type]"]' => array('value' => 'k')],
                ),
            ],
            '#default_value' => $this->getSetting('theme_background'),
        );
        $elements['button_size'] = array(
            '#type' => 'checkbox',
            '#title' => $this->t('Large button'),
            '#description' => $this->t('How would you like the button displayed?'),
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'mention']
                    ],
                    [
                        ':input[name$="[profile_structure]"]' => ['value' => 'follow']
                    ],
                    [':input[name$="[widget_type]"]' => ['value' => 'h']],
                ]
            ],
            '#default_value' => $this->getSetting('button_size'),
        );
        $elements['show_followers'] = array(
            '#type' => 'checkbox',
            '#title' => $this->t('Show followers'),
            '#description' => $this->t('Show the numbers of followers'),
            '#states' => [
                'visible' => [
                    [
                        ':input[name$="[widget_type]"]' => ['value' => 'p'],
                        ':input[name$="[profile_structure]"]' => ['value' => 'follow']
                    ]
                ]
            ],
            '#default_value' => $this->getSetting('show_followers'),
        );
        /* ALWAYS */
        $elements['iframe_css_class'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Css classes'),
            '#default_value' => $this->getSetting('iframe_css_class'),
        );
        $elements['iframe_height'] = array(
            '#type' => 'number',
            '#title' => $this->t('Height'),
            '#default_value' => $this->getSetting('iframe_height'),
            '#min' => 100,
            '#step' => 1,
        );
        return $elements;
    }

    /**
     * {@inheritdoc}
     */
    public function settingsSummary()
    {
        $summary = array();

        $css_class = $this->getSetting('css_class');
        if ($css_class) {
            $summary[] = $this->t('Width: @$css_class', array('@$css_class' => $this->getSetting('css_class')));
        }
        $height = $this->getSetting('height');
        if ($height) {
            $summary[] = $this->t('Height: @height', array('@height' => $this->getSetting('height')));
        }

        return $summary;
    }

    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {

        $element = array();
        $settings = $this->getSettings();

        foreach ($items as $delta => $item) {
            $twitter_user = SafeMarkup::checkPlain($item->value);

            switch ($settings['widget_type']) {
                case 'p':
                    $button_size = (int)$settings['button_size'] ? 'large' : 'false';
                    $show_screen_name = (int)$settings['show_screen_name'] ? 'false' : 'true';
                    $show_followers = (int)$settings['show_followers'] ? 'true' : 'false';

                    $element[$delta] = array(
                        '#theme' => 'twitter_profile_output',
                        '#css_class' => $settings['iframe_css_class'],
                        '#height' => $settings['iframe_height'],
                        '#twitter_user' => $twitter_user,
                        "#button_size" => $button_size,
                        "#show_screen_name" => $show_screen_name,
                        "#profile_structure" => $settings['profile_structure'],
                        "#tweet_text" => $settings['tweet_text'],
                        "#show_followers" => $show_followers,
                        "#theme_background" => $settings['theme_background'],
                        "#link_color" => $settings['link_color'],
                        '#attached' => [
                            'library' => ['twitter_widget/widgets'],
                        ]
                    );
                    break;
                case 'c':
                    $element[$delta] = array(
                        '#theme' => 'twitter_collection_output',
                        '#css_class' => $settings['iframe_css_class'],
                        '#height' => $settings['iframe_height'],
                        '#twitter_user' => $twitter_user,
                        "#collection_id" => $settings['collection_id'],
                        "#collection_structure" => $settings['collection_structure'],
                        "#theme_background" => $settings['theme_background'],
                        "#collection_cant" => $settings['collection_cant'],
                        "#link_color" => $settings['link_color'],
                        '#attached' => [
                            'library' => ['twitter_widget/widgets'],
                        ]
                    );
                    break;
                case 'l':
                    $element[$delta] = array(
                        '#theme' => 'twitter_list_output',
                        '#css_class' => $settings['iframe_css_class'],
                        '#height' => $settings['iframe_height'],
                        '#twitter_user' => $twitter_user,
                        '#list' => $settings['list'],
                        "#theme_background" => $settings['theme_background'],
                        "#link_color" => $settings['link_color'],
                        '#attached' => [
                            'library' => ['twitter_widget/widgets'],
                        ]
                    );
                    break;
                case 'k':
                    $element[$delta] = array(
                        '#theme' => 'twitter_likes_output',
                        '#css_class' => $settings['iframe_css_class'],
                        '#height' => $settings['iframe_height'],
                        '#twitter_user' => $twitter_user,
                        "#theme_background" => $settings['theme_background'],
                        "#link_color" => $settings['link_color'],
                        '#attached' => [
                            'library' => ['twitter_widget/widgets'],
                        ]
                    );
                    break;
                case 'h':
                    $button_size = (int)$settings['button_size'] ? 'large' : 'false';

                    $element[$delta] = array(
                        '#theme' => 'twitter_tweet_hashtag_output',
                        '#css_class' => $settings['iframe_css_class'],
                        '#height' => $settings['iframe_height'],
                        '#hashtag' => $twitter_user,
                        "#button_size" => $button_size,
                        "#tweet_text" => $settings['tweet_text'],
                        '#attached' => [
                            'library' => ['twitter_widget/widgets'],
                        ]
                    );
                    break;
                default:
                    break;
            }
        }
        return $element;
    }
}
