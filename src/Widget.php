<?php

namespace Jft;

class Widget extends \WP_Widget
{
    public function __construct()
    {
        $widgetOps = array(
            'classname' => 'widget',
            'description' => 'Displays the Just For Today',
        );
        parent::__construct('widget', 'Fetch JFT', $widgetOps);
    }

    public function widget($args, $instance): void
    {
        $reading = new Reading();
        echo $args['before_widget'];
        if (! empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        echo $reading->renderReading();
        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('Title', 'text_domain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_attr_e('Title:', 'text_domain'); ?>
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($newInstance, $oldInstance): array
    {
        $instance = array();
        $instance['title'] = (! empty($newInstance['title']) ) ? strip_tags($newInstance['title']) : '';
        return $instance;
    }
}
