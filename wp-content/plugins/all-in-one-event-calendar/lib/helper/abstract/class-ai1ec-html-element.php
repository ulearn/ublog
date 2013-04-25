<?php

/**
 *
 * @author nicola
 *
 */
abstract class Ai1ec_Html_Element implements Ai1ec_Renderable {
	/**
	 *
	 * @var string
	 */
	protected $id;
	/**
	 *
	 * @var array
	 */
	protected $classes = array();
	/**
	 *
	 * @var Ai1ec_Template_Adapter
	 */
	protected $template_adapter;

	public function __construct() {
		$this->template_adapter = Ai1ec_Adapters_Factory::create_template_adapter_instance();
	}

	/**
	 * MAgic method that renders the object as html
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render_as_html();
	}
	/**
	 *
	 * @param $id string
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Adds an element to the class array
	 *
	 * @param string $class
	 */
	public function add_class( $class ) {
		$this->classes[] = $class;
	}

	/**
	 * Creates the markup to be used to create classes
	 *
	 * @return string
	 */
	protected function create_class_markup() {
		if (empty( $this->classes )) {
			return '';
		}
		$classes = $this->template_adapter->escape_attribute(
			implode( ' ', $this->classes )
		);
		return "class='$classes'";
	}

	/**
	 * Creates the markup for an attribute
	 *
	 * @param string $attribute_name
	 * @param string $attribute_value
	 * @return string
	 */
	protected function create_attribute_markup(
		$attribute_name,
		$attribute_value
	) {
		if (empty( $attribute_value )) {
			return '';
		}
		$attribute_value = $this->template_adapter->escape_attribute( $attribute_value );
		return "$attribute_name='$attribute_value'";
	}

	/**
	 * Return the content as html instead of echoing it.
	 *
	 * @return string
	 */
	public function render_as_html() {
		ob_start();
		$this->render();
		return ob_get_clean();
	}
}
