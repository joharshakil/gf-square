<?php
/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace SquareConnect\Model;

use \ArrayAccess;
/**
 * CatalogModifierList Class Doc Comment
 *
 * @category Class
 * @package  SquareConnect
 * @author   Square Inc.
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 * @link     https://squareup.com/developers
 */
class CatalogModifierList implements ArrayAccess
{
    /**
      * Array of property to type mappings. Used for (de)serialization 
      * @var string[]
      */
    static $swaggerTypes = array(
        'name' => 'string',
        'ordinal' => 'int',
        'selection_type' => 'string',
        'modifiers' => '\SquareConnect\Model\CatalogObject[]'
    );
  
    /** 
      * Array of attributes where the key is the local name, and the value is the original name
      * @var string[] 
      */
    static $attributeMap = array(
        'name' => 'name',
        'ordinal' => 'ordinal',
        'selection_type' => 'selection_type',
        'modifiers' => 'modifiers'
    );
  
    /**
      * Array of attributes to setter functions (for deserialization of responses)
      * @var string[]
      */
    static $setters = array(
        'name' => 'setName',
        'ordinal' => 'setOrdinal',
        'selection_type' => 'setSelectionType',
        'modifiers' => 'setModifiers'
    );
  
    /**
      * Array of attributes to getter functions (for serialization of requests)
      * @var string[]
      */
    static $getters = array(
        'name' => 'getName',
        'ordinal' => 'getOrdinal',
        'selection_type' => 'getSelectionType',
        'modifiers' => 'getModifiers'
    );
  
    /**
      * $name A searchable name for the `CatalogModifierList`. This field has max length of 255 Unicode code points.
      * @var string
      */
    protected $name;
    /**
      * $ordinal Determines where this `CatalogModifierList` appears in a list of `CatalogModifierList` values.
      * @var int
      */
    protected $ordinal;
    /**
      * $selection_type Indicates whether multiple options from the `CatalogModifierList` can be applied to a single `CatalogItem`. See [CatalogModifierListSelectionType](#type-catalogmodifierlistselectiontype) for possible values
      * @var string
      */
    protected $selection_type;
    /**
      * $modifiers The options included in the `CatalogModifierList`. You must include at least one `CatalogModifier`. Each CatalogObject must have type `MODIFIER` and contain `CatalogModifier` data.
      * @var \SquareConnect\Model\CatalogObject[]
      */
    protected $modifiers;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property value initializing the model
     */
    public function __construct(array $data = null)
    {
        if ($data != null) {
            if (isset($data["name"])) {
              $this->name = $data["name"];
            } else {
              $this->name = null;
            }
            if (isset($data["ordinal"])) {
              $this->ordinal = $data["ordinal"];
            } else {
              $this->ordinal = null;
            }
            if (isset($data["selection_type"])) {
              $this->selection_type = $data["selection_type"];
            } else {
              $this->selection_type = null;
            }
            if (isset($data["modifiers"])) {
              $this->modifiers = $data["modifiers"];
            } else {
              $this->modifiers = null;
            }
        }
    }
    /**
     * Gets name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
  
    /**
     * Sets name
     * @param string $name A searchable name for the `CatalogModifierList`. This field has max length of 255 Unicode code points.
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Gets ordinal
     * @return int
     */
    public function getOrdinal()
    {
        return $this->ordinal;
    }
  
    /**
     * Sets ordinal
     * @param int $ordinal Determines where this `CatalogModifierList` appears in a list of `CatalogModifierList` values.
     * @return $this
     */
    public function setOrdinal($ordinal)
    {
        $this->ordinal = $ordinal;
        return $this;
    }
    /**
     * Gets selection_type
     * @return string
     */
    public function getSelectionType()
    {
        return $this->selection_type;
    }
  
    /**
     * Sets selection_type
     * @param string $selection_type Indicates whether multiple options from the `CatalogModifierList` can be applied to a single `CatalogItem`. See [CatalogModifierListSelectionType](#type-catalogmodifierlistselectiontype) for possible values
     * @return $this
     */
    public function setSelectionType($selection_type)
    {
        $this->selection_type = $selection_type;
        return $this;
    }
    /**
     * Gets modifiers
     * @return \SquareConnect\Model\CatalogObject[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }
  
    /**
     * Sets modifiers
     * @param \SquareConnect\Model\CatalogObject[] $modifiers The options included in the `CatalogModifierList`. You must include at least one `CatalogModifier`. Each CatalogObject must have type `MODIFIER` and contain `CatalogModifier` data.
     * @return $this
     */
    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     * @param  integer $offset Offset 
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
  
    /**
     * Gets offset.
     * @param  integer $offset Offset 
     * @return mixed 
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
  
    /**
     * Sets value based on offset.
     * @param  integer $offset Offset 
     * @param  mixed   $value  Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }
  
    /**
     * Unsets offset.
     * @param  integer $offset Offset 
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
  
    /**
     * Gets the string presentation of the object
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) {
            return json_encode(\SquareConnect\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
        } else {
            return json_encode(\SquareConnect\ObjectSerializer::sanitizeForSerialization($this));
        }
    }
}
