<?php

namespace Topdata\TopdataFoundationSW6\Trait;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;

/**
 * 01/2024 created
 */
trait CliStyleTrait
{
    protected CliStyle $cliStyle;

    /**
     * it calls setCliStyle() for all properties of this class that have this setter
     *
     * 01/2024 created
     *
     * @param CliStyle $cliStyle
     * @return void
     */
    public function setCliStyle(CliStyle $cliStyle): void
    {
        $this->cliStyle = $cliStyle;
        // ---- call `setCliStyle` for all properties of this class that have this setter
        $properties = get_object_vars($this);
        foreach ($properties as $prop) {
            if (is_object($prop) && method_exists($prop, 'setCliStyle')) {
                $prop->setCliStyle($cliStyle);
            }
        }
    }

    /**
     * should be called in the constructor
     *
     * 01/2024 created
     *
     * @return void
     */
    function beQuiet(): void
    {
        $this->cliStyle = new CliStyle(new ArrayInput([]), new NullOutput());

        // ---- call `beQuiet` for all properties of this class that have this method
        $properties = get_object_vars($this);
        foreach ($properties as $prop) {
            if (is_object($prop) && method_exists($prop, 'beQuiet')) {
                $prop->beQuiet();
            }
        }

    }


    /**
     * Should be called in the constructor
     *
     * 01/2024 created
     * 07/2024 updated to check for verbose options
     *
     * @return void
     */
    function beVerboseOnCli(): void
    {
        $this->cliStyle = UtilCli::getCliStyle();

        // ---- call `beVerboseOnCli` for all properties of this class that have this method
        $properties = get_object_vars($this);
        foreach ($properties as $prop) {
            if (is_object($prop) && method_exists($prop, 'beVerboseOnCli')) {
                $prop->beVerboseOnCli();
            }
        }
    }


}