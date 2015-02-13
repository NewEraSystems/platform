<?php

namespace Oro\Component\Layout;

/**
 * Provides a set of methods to manipulate the layout and apply the changes on demand
 *
 * NOTES: we have to re-declare all methods from {@see LayoutManipulatorInterface} here
 * because in other case "@return self" points to {@see LayoutManipulatorInterface}
 * rather than {@see DeferredLayoutManipulatorInterface}.
 * But it is important for a client code because this interface provides "fluent" operations.
 */
interface DeferredLayoutManipulatorInterface extends LayoutManipulatorInterface
{
    /**
     * Adds a new item to the layout
     *
     * @param string                    $id        The item id
     * @param string|null               $parentId  The parent item id or alias
     * @param string|BlockTypeInterface $blockType The block type associated with the item
     * @param array                     $options   The item options
     * @param string|null               $siblingId The id or alias of an item which should be nearest neighbor
     * @param bool                      $prepend   Determines whether the moving item should be located before or after
     *                                             the specified sibling item
     *
     * @return self
     */
    public function add(
        $id,
        $parentId,
        $blockType,
        array $options = [],
        $siblingId = null,
        $prepend = false
    );

    /**
     * Removes the item from the layout
     *
     * @param string $id The item id
     *
     * @return self
     */
    public function remove($id);

    /**
     * Moves the item to another location
     *
     * @param string      $id        The id or alias of item to be moved
     * @param string|null $parentId  The id or alias of a parent item the specified item is moved to
     *                               If this parameter is null only the order of the item is changed
     * @param string|null $siblingId The id or alias of an item which should be nearest neighbor
     * @param bool        $prepend   Determines whether the moving item should be located before or after
     *                               the specified sibling item
     *
     * @return self
     */
    public function move($id, $parentId = null, $siblingId = null, $prepend = false);

    /**
     * Creates an alias for the specified item
     *
     * @param string $alias A string that can be used instead of the item id
     * @param string $id    The item id
     *
     * @return self
     */
    public function addAlias($alias, $id);

    /**
     * Removes the item alias
     *
     * @param string $alias The item alias
     *
     * @return self
     */
    public function removeAlias($alias);

    /**
     * Adds a new option or updates a value of existing option for the item
     *
     * @param string $id          The item id
     * @param string $optionName  The option name
     * @param mixed  $optionValue The option value
     *
     * @return self
     */
    public function setOption($id, $optionName, $optionValue);

    /**
     * Removes the option for the item
     *
     * @param string $id         The item id
     * @param string $optionName The option name
     *
     * @return self
     */
    public function removeOption($id, $optionName);

    /**
     * Changes the block type for the item
     *
     * @param string                    $id              The item id
     * @param string|BlockTypeInterface $blockType       The new block type associated with the item
     * @param callable|null             $optionsCallback The callback function is called before
     *                                                   the block type is changed
     *                                                   This function can be used to provide options
     *                                                   for the new block type
     *                                                   signature: function (array $options) : array
     *                                                   $options argument contains current options
     *                                                   returned array contains new options
     *
     * @return self
     */
    public function changeBlockType($id, $blockType, $optionsCallback = null);

    /**
     * Sets the theme(s) to be used for rendering the layout item and its children
     *
     * @param string|string[] $themes The theme(s). For example 'MyBundle:Layout:my_theme.html.twig'
     * @param string|null     $id     The id of the layout item to assign the theme(s) to
     *
     * @return self
     */
    public function setBlockTheme($themes, $id = null);

    /**
     * Reverts the manipulator to the initial state
     *
     * @return self
     */
    public function clear();

    /**
     * Returns the number of added items
     *
     * @return int
     */
    public function getNumberOfAddedItems();

    /**
     * Applies all scheduled changes
     *
     * @param ContextInterface $context  The context
     * @param boolean          $finalize This flag determines whether the manipulator should check
     *                                   for all actions were executed.
     *                                   False means that all not executed actions should be kept.
     *                                   True means that not executed actions are the reason for an error.
     *
     * @throws Exception\DeferredUpdateFailureException if not all scheduled action have been performed
     */
    public function applyChanges(ContextInterface $context, $finalize = false);
}
