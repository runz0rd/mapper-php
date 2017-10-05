<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 01/06/17
 * Time: 22:07
 */

namespace Reflection;

class TokenParser extends \Doctrine\Common\Annotations\TokenParser {

    public function parseUseStatements($namespaceName) {
        $statements = array();
        while (($token = $this->next())) {
            // avoid trait use statements that would replace the ones before class token
            if($token[0] === T_CLASS) {
                break;
            }
            if ($token[0] === T_USE) {
                $statements = array_merge($statements, $this->parseUseStatement());
                continue;
            }
            if ($token[0] !== T_NAMESPACE || $this->parseNamespace() != $namespaceName) {
                continue;
            }

            // Get fresh array for new namespace. This is to prevent the parser to collect the use statements
            // for a previous namespace with the same name. This is the case if a namespace is defined twice
            // or if a namespace with the same name is commented out.
            $statements = array();
        }

        return $statements;
    }

}