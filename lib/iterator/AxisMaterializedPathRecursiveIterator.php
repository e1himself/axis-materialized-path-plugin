<?php
/**
 * Date: 10.10.12
 * Time: 0:44
 * Author: Ivan Voskoboynyk
 */
class MaterializedPathRecursiveIterator implements RecursiveIterator
{
  /**
   * @var axisTreeNode
   */
  protected $topNode = null;

  /**
   * @var axisTreeNode
   */
  protected $curNode = null;

  /**
   * @param $node axisTreeNode
   */
  public function __construct($node)
  {
    $this->topNode = $node;
    $this->curNode = $node;
  }

  public function rewind()
  {
    $this->curNode = $this->topNode;
  }

  public function valid()
  {
    return ($this->curNode !== null);
  }

  public function current()
  {
    return $this->curNode;
  }

  public function key()
  {
    $method = method_exists($this->curNode, 'getPath') ? 'getPath' : 'getAncestors';
    $key = array();
    foreach ($this->curNode->$method() as $node) {
      $key[] = $node->getPrimaryKey();
    }

    return implode('.', $key);
  }

  public function next()
  {
    $nextNode = null;
    $method = method_exists($this->curNode, 'retrieveNextSibling') ? 'retrieveNextSibling' : 'getNextSibling';
    if ($this->valid()) {
      while (null === $nextNode) {
        if (null === $this->curNode) {
          break;
        }

        if ($this->curNode->hasNextSibling()) {
          $nextNode = $this->curNode->$method();
        } else {
          break;
        }
      }
      $this->curNode = $nextNode;
    }

    return $this->curNode;
  }

  public function hasChildren()
  {
    return $this->curNode->hasChildren();
  }

  public function getChildren()
  {
    $method = method_exists($this->curNode, 'retrieveFirstChild') ? 'retrieveFirstChild' : 'getFirstChild';

    return new NestedSetRecursiveIterator($this->curNode->$method());
  }
}
