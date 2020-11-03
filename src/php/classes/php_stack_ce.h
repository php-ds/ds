#ifndef PHP_DS_STACK_CE_H
#define PHP_DS_STACK_CE_H

#include "php.h"
#include "../../common.h"
#include "../arginfo.h"

extern zend_class_entry *php_ds_stack_ce;

ARGINFO_OPTIONAL_ZVAL(          Stack___construct, values);
ARGINFO_LONG(                   Stack_allocate, capacity);
ARGINFO_NONE_RETURN_LONG(       Stack_capacity);
ARGINFO_VARIADIC_ZVAL(          Stack_push, values);
ARGINFO_NONE(                   Stack_pop);
ARGINFO_NONE(                   Stack_peek);
ARGINFO_NONE_RETURN_OBJ(        Stack_getIterator, Traversable);

ARGINFO_ZVAL_RETURN_BOOL(       Stack_offsetExists, offset);
ARGINFO_ZVAL(                   Stack_offsetGet, offset);
ARGINFO_ZVAL_ZVAL(              Stack_offsetSet, offset, value);
ARGINFO_ZVAL(                   Stack_offsetUnset, offset);

void php_ds_register_stack();

#endif
