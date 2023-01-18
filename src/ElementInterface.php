<?php
/**
 * ElementInterface
 *
 * @package WPTelegram\FormatText
 */

namespace WPTelegram\FormatText;

interface ElementInterface {

	/**
	 * Get the DOM Node.
	 */
	public function getNode();

	/**
	 * Check if the element is a block element
	 *
	 * @return bool - Whether the element is a block element
	 */
	public function isBlock();

	/**
	 * Check if the element is a text element
	 *
	 * @return bool - Whether the element is a text element
	 */
	public function isText();

	/**
	 * Check if the element is whitespace
	 *
	 * @return bool - Whether the element is whitespace
	 */
	public function isWhitespace();

	/**
	 * Get the tag name of the element
	 *
	 * @return string - The tag name of the element
	 */
	public function getTagName();

	/**
	 * Get the value of the element
	 *
	 * @return string - The value of the element
	 */
	public function getValue();

	/**
	 * Check if the element has a parent
	 *
	 * @return bool - Whether the element has a parent
	 */
	public function hasParent();

	/**
	 * Get the parent element
	 *
	 * @return ElementInterface|null - The parent element, or null if it does not have one
	 */
	public function getParent();

	/**
	 * Get the next sibling element
	 *
	 * @return ElementInterface|null - The next sibling element, or null if it does not have one
	 */
	public function getNextSibling();

	/**
	 * Get the previous sibling element
	 *
	 * @return ElementInterface|null - The previous sibling element, or null if it does not have one
	 */
	public function getPreviousSibling();

	/**
	 * Check if the element is a descendant of an element with the given tag name(s)
	 *
	 * @param string|string[] $tagNames - The tag name(s) to check for.
	 * @return bool - Whether the element is a descendant of an element with the given tag name(s)
	 */
	public function isDescendantOf( $tagNames );

	/**
	 * Check if the element has children
	 *
	 * @return bool - Whether the element has children
	 */
	public function hasChildren();

	/**
	 * Get an array of the element's children
	 *
	 * @return ElementInterface[] - An array of the element's children
	 */
	public function getChildren();

	/**
	 * Get the next element in the document
	 *
	 * @return ElementInterface|null - The next element in the document, or null if it does not have one
	 */
	public function getNext();

	/**
	 * Get the element's position among its siblings
	 *
	 * @return int - The element's position among its siblings
	 */
	public function getSiblingPosition();

	/**
	 * Get a string representation of the element's children
	 *
	 * @return string - A string representation of the element's children
	 */
	public function getChildrenAsString();

	/**
	 * Used to determine the level of the list item.
	 */
	public function getListItemLevel();

	/**
	 * Get the attribute by name
	 *
	 * @param string $name - The name of the attribute.
	 * @return string - The value of the attribute
	 */
	public function getAttribute( $name);

	/**
	 * Set the final output for this node.
	 *
	 * @param string $content The final output.
	 */
	public function setFinalOutput( $content );

	/**
	 * Get the next element node.
	 */
	public function getNextElement();
}
