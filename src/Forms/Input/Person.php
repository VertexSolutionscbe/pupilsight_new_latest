<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\Traits\MultipleOptionsTrait;
use Pupilsight\Contracts\Database\Connection;

/**
 * Person
 *
 * @version v18
 * @since   v18
 */
class Person extends Select
{
    protected $displayPhoto = true;

    public function photo($value)
    {
        $this->displayPhoto = $value;
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        $this->addClass('personSelect');

        $output = '';
        $output .= '<div class="w-full flex justify-end items-center pl-24 lg:pl-0">';
        if ($this->displayPhoto) {

            $output .= '<div id="'.$this->getID().'Photo" class="flex-none relative w-20 h-20 z-10 -ml-24 mr-4 rounded-full bg-gray border border-solid border-gray bg-no-repeat" style="width:36px;height:36px;">';
            $output .= '<div id="'.$this->getID().'Count" class="hidden"></div>';
            $output .= '</div>';

            $output .= '<script>
            $(function(){
                $("#'.$this->getID().'").on("input", function() {
                    var value =  $(this).val();

                    if ( Array.isArray(value) && value.length > 1) {
                        value = value.filter(function (value, index, self) { 
                            return self.indexOf(value) === index;
                        });

                        $("#'.$this->getID().'Count").show();
                        $("#'.$this->getID().'Count").html(value.length);
                        $("#'.$this->getID().'Photo")
                            .css("background-image" , "url(./themes/Default/img/attendance_large.png)")
                            .css("background-size", "cover")
                            .css("width", "36px")
                            .css("height", "36px")
                            .css("background-position", "50% 45%");

                        return;
                    } else {
                        $("#'.$this->getID().'Count").hide();
                    }
                    var personID = Array.isArray(value) ? value[0] : value;
                    $.ajax({
                        url: "./modules/User Admin/user_manage_userPhotoAjax.php",
                        data: { pupilsightPersonID: personID, },
                        type: "POST",
                        success: function(data) {
                            $("#'.$this->getID().'Count").html("");
                            $("#'.$this->getID().'Photo")
                                .css("background-image" , "url(./"+data+")")
                                .css("background-size", "cover")
                                .css("width", "36px")
                                .css("height", "36px")
                                .css("background-position", "50% 20%");
                        }
                    });
                });

                var value =  $("#'.$this->getID().'").val();
                if (value != "" && value != "Please select...") {
                    $("#'.$this->getID().'").trigger("input");
                }
            });
            </script>';
        }

        $output .= parent::getElement();

        $output .= '</div>';

        return $output;
    }
}
