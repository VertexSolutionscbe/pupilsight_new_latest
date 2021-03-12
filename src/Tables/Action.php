<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables;

use Pupilsight\Forms\Layout\WebLink;

/**
 * Action
 *
 * @version v16
 * @since   v16
 */
class Action extends WebLink
{
    protected $name;
    protected $label;
    protected $url;
    protected $icon;
    protected $params = array();

    protected $modal = false;
    protected $direct = false;
    protected $external = false;
    protected $displayLabel = false;

    public function __construct($name, $label = '')
    {
        $this->name = $name;
        $this->setLabel($label);

        // Pre-defined settings for common actions
        switch ($this->name) {
            case 'add':     $this->setIcon('page_new');
                            break;
            case 'addMultiple':
                            $this->setIcon('page_new_multi');
                            break;
            case 'edit':    $this->setIcon('config');
                            break;
            case 'editAlert':  $this->setIcon('config');
                        break;
            case 'editnew':  $this->setIcon('config')->modalWindowNew(650, 100);
                            break;
            case 'uploadtemplate':  $this->setIcon('config')->modalWindowNew(650, 100);
                            break;
            case 'applicationtemplate':  $this->setIcon('config');
                        break;
            case 'registereduser':  $this->setIcon('config');
                        break;  
            case 'reason':  $this->setIcon('config')->modalWindowNew(650, 100);
                            break;                
            case 'copynew':  $this->setIcon('config')->modalWindowNew(650, 100);
                        break;                                
            case 'delete':  $this->setIcon('garbage')->modalWindow(650, 200);
                            break;
            case 'deletenew':  $this->setIcon('garbage')->modalWindow(650, 135);
                            break;
            case 'deleteAlert':  $this->setIcon('garbage')->modalWindow(650, 135);
                            break;     
            case 'deleteStaff':  $this->setIcon('garbage')->modalWindow(650, 400);
                        break;
            case 'print':   $this->setIcon('print');
                            break;
            case 'export':  $this->setIcon('download');
                            break;
            case 'import':  $this->setIcon('upload');
                            break;
            case 'view':    $this->setIcon('zoom');
                            break;
            case 'list':    $this->setIcon('list');
                            break; 
            case 'assign':  $this->setIcon('list');
                            break;  
            case 'amountconfig':  $this->setIcon('list');
                        break;     
            case 'form':    $this->setIcon('zoom');
                        break;      
            case 'printInvoice':  $this->setIcon('config');
            break;                                                 
        }
    }

    /**
     * Sets the internal url for this action.
     * 
     * @param string $url
     * @return self
     */
    public function setURL($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Sets the external url for this action.
     * 
     * @param string $url
     * @return self
     */
    public function setExternalURL($url)
    {
        $this->url = $url;
        $this->external = true;
        $this->target = '_blank';

        return $this;
    }

    /**
     * Sets the action label, displayed on hover.
     *
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Gets the action label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Display the action label as text next to the icon.
     *
     * @param bool $value
     * @return self
     */
    public function displayLabel($value = true)
    {
        $this->displayLabel = $value;

        return $this;
    }

    /**
     * Set the icon name, without any path or filetype
     *
     * @param string $icon
     * @return self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Gets the action icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @deprecated Remove setters that start with isXXX for code consistency.
     */
    public function isModal($width = 650, $height = 650) 
    {
        return $this->modalWindow($width, $height);
    }

    /**
     * Load the action URL in a modal window rather than loading a new page. Commonly used for delete actions.
     *
     * @param bool $value
     * @return self
     */
    public function modalWindow($width = 650, $height = 650) 
    {
        $this->modal = true;
        $this->setClass('thickbox')
            ->addParam('width', $width)
            ->addParam('height', $height);

        return $this;
    }

    public function modalWindowNew($width = 650, $height = 650) 
    {
        $this->modal = true;
        $this->setClass('thickbox');
        // $this->setClass('thickbox')
        //     ->addParam('width', $width)
        //     ->addParam('height', $height);

        return $this;
    }

    /**
     * @deprecated Remove setters that start with isXXX for code consistency.
     */
    public function isDirect($value = true) 
    {
        return $this->directLink($value);
    }

    /**
     * The action link will not prepend an index.php?q=
     *
     * @return self
     */
    public function directLink($value = true) 
    {
        $this->direct = $value;

        return $this;
    }

    /**
     * Renders the action as an icon and url, adding in any nessesary url parameters.
     *
     * @param array $data
     * @param array $params
     * @return string
     */
    public function getOutput(&$data = array(), $params = array())
    {
        global $guid; // :(

        if ($icon = $this->getIcon()) {
            //echo $this->getLabel();
			if($this->getLabel() == 'Add'){
				$this->setContent(sprintf('%1$s ', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }

            elseif($this->getLabel() == 'Add Campaign'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-plus-circle-outline"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }
            elseif($this->getLabel() == 'Work Flow'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-sitemap mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } 
            elseif($this->getLabel() == 'Apply Here'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-plus-circle-outline"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }
            elseif($this->getLabel() == 'Submitted Form'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-format-list-bulleted mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }
            elseif($this->getLabel() == 'Add Multiple'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-plus-circle-outline  mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
			} 
            elseif($this->getLabel() == 'Edit'|| $this->getLabel() == 'edit'||$this->getLabel() == 'Edit Individual Needs Details' || $this->getLabel() == 'EditAlert'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-pencil-box-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Copy'){
                $this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-content-copy mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Duplicate'){
                    $this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-content-copy mdi-24px"></i>', 
                        ($this->displayLabel? $this->getLabel() : ''),
                        $this->getLabel(), 
                        $this->getIcon()
                    ));
			} elseif($this->getLabel() == 'Delete'){
               
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-trash-can-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
                
			} elseif($this->getLabel() == 'DeleteStaff'){
               
				$this->setContent(sprintf('%1$s <i title="Delete Staff" class="mdi mdi-trash-can-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
                
			} elseif($this->getLabel() == 'DeleteAlert'){
               
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-trash-can-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
                
			} elseif($this->getLabel() == 'DeleteNew'){
               
				$this->setContent(sprintf('%1$s <i title="Delete Class Teacher" class="mdi mdi-close-thick mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
                
			} elseif($this->getLabel() == 'Cancel'){
               
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-trash-can-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
                
			} elseif($this->getLabel() == 'Import'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-cloud-upload-outline mdi-24px mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
			} elseif($this->getLabel() == 'Change Password'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-key-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
			} elseif($this->getLabel() == 'View Details' || $this->getLabel() == 'View'|| $this->getLabel() == 'View Application Form'  || $this->getLabel() == 'Reason' | $this->getLabel() == 'Show History' ){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-eye-outline mdi-24px mdi-24px mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
			} elseif($this->getLabel() == 'Form'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-clipboard-list-outline  mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
			} elseif($this->getLabel() == 'Issue'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-arrow-right-circle-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
			}  elseif($this->getLabel() == 'Preview Invoice'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-printer mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Print Invoice'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-printer mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } 
            //<i class="mdi mdi-lock-open-outline"></i>
            elseif($this->getLabel() == 'Open'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-lock-open-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } 
            //<i class="mdi mdi-clipboard-account-outline"></i>
            elseif($this->getLabel() == 'Assign to Class'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-clipboard-account-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Counter Used by'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-clipboard-account-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }  elseif($this->getLabel() == 'Copy All To Next Year'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-content-copy mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } 
            elseif($this->getLabel() == 'Assign Houses'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-account-circle-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Amount Config'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-currency-inr mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Grade Configure'  || $this->getLabel() == 'Sketch Configure'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-cog-outline mdi-24px" aria-hidden="true"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Generate Sketch'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-briefcase-plus mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            } elseif($this->getLabel() == 'Upload Template'){
               
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-cloud-upload-outline mdi-24px mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
                
			} elseif($this->getLabel() == 'Registered User'){
				$this->setContent(sprintf('%1$s <i title="%2$s" class="mdi mdi-clipboard-account-outline mdi-24px"></i>', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }
            else {
				$this->setContent(sprintf('%1$s<img title="%2$s" src="'.$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/%3$s.png" width="25" height="25" class="ml-1">', 
                    ($this->displayLabel? $this->getLabel() : ''),
                    $this->getLabel(), 
                    $this->getIcon()
                ));
            }
            
            
           

			
			
            
        } else {
            $this->setContent($this->getLabel());
        }

        $queryParams = !$this->direct ? array('q' => $this->url) : array();

        // Allow ActionColumn level params to auto-fill from the row data, if they're not set
        foreach ($params as $key => $value) {
            $queryParams[$key] = (is_null($value) && !empty($data[$key]))? $data[$key] : $value;
        }

        // Load excplicit params from the Action itself
        foreach ($this->params as $key => $value) {
            $queryParams[$key] = $value;
        }
        if ($this->external) {
            $this->setAttribute('href', $this->url);
        } else if ($this->direct) {
            $this->setAttribute('href', $_SESSION[$guid]['absoluteURL'].$this->url.'?'.http_build_query($queryParams));
        } else if ($this->modal) {
            $this->setAttribute('href', $_SESSION[$guid]['absoluteURL'].'/fullscreen.php?'.http_build_query($queryParams));
        } else {
            $this->setAttribute('href', $_SESSION[$guid]['absoluteURL'].'/index.php?'.http_build_query($queryParams));
        }

        return parent::getOutput();
    }
}
