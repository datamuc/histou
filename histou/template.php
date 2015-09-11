<?php
/**
Contains types of Templates.
PHP version 5
@category Template_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
require_once 'histou/rule.php';
require_once 'histou/templateParser.php';
trait EnableLambdas
{
    /**
    Enables Lambdas.
    @param string $name name.
    @param string $args args.
    @return object.
    **/
    public function __call($name, $args)
    {
        return call_user_func_array($this->$name, $args);
    }
}
/**
Base Class Template.
PHP version 5
@category Template_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Template
{
    use EnableLambdas;
    private $_file;
    private $_rule;
    private $_genTemplate;

    /**
    Creates a template.
    @param string  $file        Path to the templatefile.
    @param object  $rule        ruleset.
    @param closure $genTemplate lambda to create dashboard.
    @return object.
    **/
    public function __construct($file, Rule $rule, closure $genTemplate)
    {
        $this->_file = $file;
        $this->_rule = $rule;
        $this->_genTemplate = $genTemplate;
    }

    /**
    Returns the path to the template.
    @return string.
    **/
    public function getPath()
    {
        return dirname($this->_file);
    }

    /**
    Returns the whole filename.
    @return string.
    **/
    public function getFileName()
    {
        return basename($this->_file);
    }

    /**
    Returns the Filename, without extension.
    @return string.
    **/
    public function getSimpleFileName()
    {
        return strstr($this->getFileName(), '.', true);
    }

    /**
    Calls the template lambda and returns the dashboard.
    @param array $perfData array of performance data from influxdb.
    @return object.
    **/
    public function getTemplate($perfData)
    {
        return $this->_genTemplate($perfData);
    }

    /**
    Prints the Template in a nice format.
    @return string.
    **/
    public function __toString()
    {
        return "File:\t".$this->_file."\nRule: ".$this->_rule;
    }

    /**
    Tests if a Template matches the given Tablename.
    @param string $tablename Name to test.
    @return boolean.
    **/
    public function matchesTablename($tablename)
    {
        return $this->_rule->matchesTablename($tablename);
    }

    /**
    Sort function to order the objects
    @param object $first  Template1.
    @param object $second Template2.
    @return int.
    **/
    public static function compare($first, $second)
    {
        return Rule::compare($first->_rule, $second->_rule);
    }

    /**
    Test if the rule of the template is valid.
    @return boolean.
    **/
    public function isValid()
    {
        return $this->_rule->isValid() == 0 ? true : false;
    }

    /**
    Searches for the given default template in the template list.
    @param array  $templates   List of templates.
    @param string $defaultName Filename.
    @return object.
    **/
    public static function findDefaultTemplate($templates, $defaultName)
    {
        foreach (array_reverse($templates) as $template) {
            if ($template->getFileName() == $defaultName) {
                return $template;
            }
        }
    }
}

/**
Inheritate from Template for simple Templatefiles
PHP version 5
@category Template_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class SimpleTemplate extends Template
{
    /**
    Expects a filename to the simple config.
    @param string $file Path to file.
    @return object.
    **/
    function __construct($file)
    {
        $result = TemplateParser::parseSimpleTemplate($file);
        parent::__construct($file, $result[0], $result[1]);
    }
}
