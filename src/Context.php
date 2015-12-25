<?php
/*

Copyrights for code authored by Yahoo! Inc. is licensed under the following terms:
MIT License
Copyright (c) 2013-2015 Yahoo! Inc. All Rights Reserved.
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Origin: https://github.com/zordius/lightncandy
*/

/**
 * file to handle LightnCandy Context
 *
 * @package    LightnCandy
 * @author     Zordius <zordius@yahoo-inc.com>
 */

namespace LightnCandy;

use \LightnCandy\Flags;

/**
 * LightnCandy class to handle Context
 */
class Context extends Flags
{
    /**
     * Create a context from options
     *
     * @param array<string,array|string|integer> $options input options
     *
     * @return array<string,array|string|integer> Context from options
     */
    public static function create($options) {
        if (!is_array($options)) {
            $options = array();
        }

        $flags = isset($options['flags']) ? $options['flags'] : static::FLAG_BESTPERFORMANCE;

        $context = array(
            'flags' => array(
                'errorlog' => $flags & static::FLAG_ERROR_LOG,
                'exception' => $flags & static::FLAG_ERROR_EXCEPTION,
                'skippartial' => $flags & static::FLAG_ERROR_SKIPPARTIAL,
                'standalone' => $flags & static::FLAG_STANDALONEPHP,
                'noesc' => $flags & static::FLAG_NOESCAPE,
                'jstrue' => $flags & static::FLAG_JSTRUE,
                'jsobj' => $flags & static::FLAG_JSOBJECT,
                'hbesc' => $flags & static::FLAG_HBESCAPE,
                'this' => $flags & static::FLAG_THIS,
                'nohbh' => $flags & static::FLAG_NOHBHELPERS,
                'parent' => $flags & static::FLAG_PARENT,
                'echo' => $flags & static::FLAG_ECHO,
                'advar' => $flags & static::FLAG_ADVARNAME,
                'namev' => $flags & static::FLAG_NAMEDARG,
                'spvar' => $flags & static::FLAG_SPVARS,
                'slash' => $flags & static::FLAG_SLASH,
                'else' => $flags & static::FLAG_ELSE,
                'exhlp' => $flags & static::FLAG_EXTHELPER,
                'lambda' => $flags & static::FLAG_HANDLEBARSLAMBDA,
                'mustlok' => $flags & static::FLAG_MUSTACHELOOKUP,
                'mustlam' => $flags & static::FLAG_MUSTACHELAMBDA,
                'noind' => $flags & static::FLAG_PREVENTINDENT,
                'debug' => $flags & static::FLAG_RENDER_DEBUG,
                'prop' => $flags & static::FLAG_PROPERTY,
                'method' => $flags & static::FLAG_METHOD,
                'runpart' => $flags & static::FLAG_RUNTIMEPARTIAL,
                'rawblock' => $flags & static::FLAG_RAWBLOCK,
                'partnc' => $flags & static::FLAG_PARTIALNEWCONTEXT,
                'nostd' => $flags & static::FLAG_IGNORESTANDALONE,
                'strpar' => $flags & static::FLAG_STRINGPARAMS,
                'knohlp' => $flags & static::FLAG_KNOWNHELPERSONLY,
            ),
            'delimiters' => array(
                isset($options['delimiters'][0]) ? $options['delimiters'][0] : '{{',
                isset($options['delimiters'][1]) ? $options['delimiters'][1] : '}}',
            ),
            'level' => 0,
            'stack' => array(),
            'currentToken' => null,
            'error' => array(),
            'elselvl' => array(),
            'elseif' => false,
            'basedir' => static::prepareBasedir($options),
            'fileext' => static::prepareFileext($options),
            'tokens' => array(
                'standalone' => true,
                'ahead' => false,
                'current' => 0,
                'count' => 0,
                'partialind' => '',
            ),
            'usedPartial' => array(),
            'partialStack' => array(),
            'partialCode' => array(),
            'usedFeature' => array(
                'rootthis' => 0,
                'enc' => 0,
                'raw' => 0,
                'sec' => 0,
                'isec' => 0,
                'if' => 0,
                'else' => 0,
                'unless' => 0,
                'each' => 0,
                'this' => 0,
                'parent' => 0,
                'with' => 0,
                'comment' => 0,
                'partial' => 0,
                'dynpartial' => 0,
                'inlpartial' => 0,
                'hbhelper' => 0,
                'delimiter' => 0,
                'subexp' => 0,
                'rawblock' => 0,
                'lookup' => 0,
                'log' => 0,
            ),
            'usedCount' => array(
                'var' => array(),
                'hbhelpers' => array(),
                'runtime' => array(),
            ),
            'parsed' => array(),
            'partials' => (isset($options['partials']) && is_array($options['partials'])) ? $options['partials'] : array(),
            'partialblock' => array(),
            'inlinepartial' => array(),
            'hbhelpers' => array(),
            'renderex' => isset($options['renderex']) ? $options['renderex'] : '',
            'prepartial' => (isset($options['prepartial']) && is_callable($options['prepartial'])) ? $options['prepartial'] : false,
            'runtime' => isset($options['runtime']) ? $options['runtime'] : '\\LightnCandy\\Runtime',
            'safestring' => '\\LightnCandy\\SafeString',
            'rawblock' => false,
            'funcprefix' => uniqid('lcr'),
        );

        $context['ops'] = $context['flags']['echo'] ? array(
            'seperator' => ',',
            'f_start' => 'echo ',
            'f_end' => ';',
            'op_start' => 'ob_start();echo ',
            'op_end' => ';return ob_get_clean();',
            'cnd_start' => ';if ',
            'cnd_then' => '{echo ',
            'cnd_else' => ';}else{echo ',
            'cnd_end' => ';}echo ',
            'cnd_nend' => ';}',
        ) : array(
            'seperator' => '.',
            'f_start' => 'return ',
            'f_end' => ';',
            'op_start' => 'return ',
            'op_end' => ';',
            'cnd_start' => '.(',
            'cnd_then' => ' ? ',
            'cnd_else' => ' : ',
            'cnd_end' => ').',
            'cnd_nend' => ')',
        );

        $context['ops']['enc'] = $context['flags']['hbesc'] ? 'encq' : 'enc';
        static::updateHelperTable($context, $options);
        static::updateHelperTable($context, $options, 'blockhelpers');
        static::updateHelperTable($context, $options, 'hbhelpers');

        if ($context['flags']['partnc'] && ($context['flags']['runpart'] == 0)) {
            $context['error'][] = 'The FLAG_PARTIALNEWCONTEXT requires FLAG_RUNTIMEPARTIAL! Fix your compile options please';
        }

        return $context;
    }

    /**
     * prepare list of template file extensions from options
     *
     * @param array<string,array|string|integer> $options current compile option
     *
     * @return array<string> file extensions
     *
     * @expect array('.tmpl') when input array()
     * @expect array('test') when input array('fileext' => 'test')
     * @expect array('test1') when input array('fileext' => array('test1'))
     * @expect array('test2', 'test3') when input array('fileext' => array('test2', 'test3'))
     */
    protected static function prepareFileExt($options) {
        $exts = isset($options['fileext']) ? $options['fileext'] : '.tmpl';
        return is_array($exts) ? $exts : array($exts);
    }

    /**
     * prepare list of base directory from options
     *
     * @param array<string,array|string|integer> $options current compile option
     *
     * @return array<string> base directories
     *
     * @expect array() when input array()
     * @expect array() when input array('basedir' => array())
     * @expect array('src') when input array('basedir' => array('src'))
     * @expect array('src') when input array('basedir' => array('src', 'dir_not_found'))
     * @expect array('src', 'tests') when input array('basedir' => array('src', 'tests'))
     */
    protected static function prepareBaseDir($options) {
        $dirs = isset($options['basedir']) ? $options['basedir'] : 0;
        $dirs = is_array($dirs) ? $dirs : array($dirs);
        $ret = array();

        foreach ($dirs as $dir) {
            if (is_string($dir) && is_dir($dir)) {
                $ret[] = $dir;
            }
        }

        return $ret;
    }

    /**
     * update specific custom helper table from options
     *
     * @param array<string,array|string|integer> $context prepared context
     * @param array<string,array|string|integer> $options input options
     * @param string $tname helper table name
     *
     * @return array<string,array|string|integer> context with generated helper table
     *
     * @expect array() when input array(), array()
     * @expect array('flags' => array('exhlp' => 1)) when input array('flags' => array('exhlp' => 1)), array('helpers' => array('abc'))
     * @expect array('error' => array('You provide a custom helper named as \'abc\' in options[\'helpers\'], but the function abc() is not defined!'), 'flags' => array('exhlp' => 0)) when input array('error' => array(), 'flags' => array('exhlp' => 0)), array('helpers' => array('abc'))
     * @expect array('flags' => array('exhlp' => 1), 'helpers' => array('\\LightnCandy\\Runtime::raw' => '\\LightnCandy\\Runtime::raw')) when input array('flags' => array('exhlp' => 1), 'helpers' => array()), array('helpers' => array('\\LightnCandy\\Runtime::raw'))
     * @expect array('flags' => array('exhlp' => 1), 'helpers' => array('test' => '\\LightnCandy\\Runtime::raw')) when input array('flags' => array('exhlp' => 1), 'helpers' => array()), array('helpers' => array('test' => '\\LightnCandy\\Runtime::raw'))
     */
    protected static function updateHelperTable(&$context, $options, $tname = 'helpers') {
        if (isset($options[$tname]) && is_array($options[$tname])) {
            foreach ($options[$tname] as $name => $func) {
                $tn = is_int($name) ? $func : $name;
                if (is_callable($func)) {
                    $context[$tname][$tn] = $func;
                } else {
                    if (is_array($func)) {
                        $context['error'][] = "I found an array in $tname with key as $name, please fix it.";
                    } else {
                        if (!$context['flags']['exhlp']) {
                            $context['error'][] = "You provide a custom helper named as '$tn' in options['$tname'], but the function $func() is not defined!";
                        }
                    }
                }
            }
        }
        return $context;
    }

    /**
     * Merge a context into another
     *
     * @param array<string,array|string|integer> $context master context
     * @param array<string,array|string|integer> $tmp another context will be overwrited into master context
     */
    public static function merge(&$context, $tmp) {
        $context['error'] = $tmp['error'];
        $context['partialCode'] = $tmp['partialCode'];
        $context['partialStack'] = $tmp['partialStack'];
        $context['usedCount'] = $tmp['usedCount'];
        $context['usedFeature'] = $tmp['usedFeature'];
        $context['usedPartial'] = $tmp['usedPartial'];
    }
}

