# Coding Standard Rules
## From PSR2

We imported the [PSR2 Standard](./psr2.md) with this override:

- There MUST NOT be trailing whitespace at the end of blank lines
```
<rule ref="Squiz.WhiteSpace.SuperfluousWhitespace">
    <properties>
        <property name="ignoreBlankLines" value="false"/>
    </properties>
</rule>
```

## From Zend

We imported these rules, used in the Zend standard:
```
<rule ref="Generic.Functions.OpeningFunctionBraceBsdAllman">
    <exclude name="Generic.Functions.OpeningFunctionBraceBsdAllman.BraceOnSameLine"/>
</rule>
<rule ref="Generic.PHP.DisallowShortOpenTag"/>
<rule ref="PEAR.Classes.ClassDeclaration"/>
<rule ref="Squiz.Functions.GlobalFunction"/>
<rule ref="Squiz.NamingConventions.ValidVariableName">
    <exclude name="Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore"/>
    <exclude name="Squiz.NamingConventions.ValidVariableName.ContainsNumbers"/>
</rule>
```

## From symfony
From [symfony standard](http://symfony.com/doc/current/contributing/code/standards.html)

### Structure
- Add a single space after each comma delimiter

```
<rule ref="Symfony3Custom.WhiteSpace.CommaSpacing" />
```

- Add a single space around binary operators (`==`, `&&`, `...`)

```
<rule ref="Symfony3Custom.WhiteSpace.BinaryOperatorSpacing" />
```

We do not respect the exception of the concatenation (`.`) operator
```
<rule ref="Squiz.Strings.ConcatenationSpacing">
    <properties>
        <property name="spacing" value="1"/>
        <property name="ignoreNewlines" value="true" />
    </properties>
</rule>
```

- Place unary operators (`!`, `--`, `...`) adjacent to the affected variable

```
<rule ref="Symfony3Custom.WhiteSpace.SpaceUnaryOperatorSpacing" />
```

- Add a comma after each array item in a multi-line array, even after the last one

```
<rule ref="Symfony3Custom.Array.MultiLineArrayComma" />
```

- Add a blank line before return statements,
 unless the return is alone inside a statement-group (like an `if` statement)

```
<rule ref="Symfony3Custom.Formatting.BlankLineBeforeReturn" />
```

- Use `return null` when a function explicitly returns null values
 and use `return` when the function returns void values

```
<rule ref="Symfony3Custom.Commenting.FunctionComment" />
```

- Use braces to indicate control structure body regardless of the number of statements it contains

Covered by `PSR2`

- Define one class per file

Covered by `PSR2`

- Declare the class inheritance and all the implemented interfaces on the same line as the class name

Covered by `PSR2`

- Declare class properties before methods

```
<rule ref="Symfony3Custom.Classes.PropertyDeclaration" />
```

- Declare public methods first, then protected ones and finally private ones.
 The exceptions to this rule are the class constructor and the `setUp()` and `tearDown()` methods of PHPUnit tests,
  which must always be the first methods to increase readability

```
<rule ref="Symfony3Custom.Functions.ScopeOrder" />
```

- Declare all the arguments on the same line as the method/function name,
 no matter how many arguments there are

Not checked because of the limit of 120 characters per line

- Use parentheses when instantiating classes regardless of the number of arguments the constructor has

```
<rule ref="Symfony3Custom.Objects.ObjectInstantiation" />
```

- Do not use spaces around `[` offset accessor and before `]` offset accessor

```
<rule ref="Squiz.Arrays.ArrayBracketSpacing"/>
```

### Naming Conventions

- Use camelCase, not underscores, for variable, function and method names, arguments

Covered by `PSR2` and `Zend`

- Use namespaces for all classes

Covered by `PSR1`
```
<rule ref="Squiz.Classes.ValidClassName" />
```

- Prefix abstract classes with `Abstract`

```
<rule ref="Symfony3Custom.NamingConventions.ValidClassName" />
```

- Suffix interfaces with `Interface`

```
<rule ref="Symfony3Custom.NamingConventions.ValidClassName" />
```

- Suffix traits with `Trait`

```
<rule ref="Symfony3Custom.NamingConventions.ValidClassName" />
```

- Suffix exceptions with `Exception`

```
<rule ref="Symfony3Custom.NamingConventions.ValidClassName" />
```

- For type-hinting in PHPDocs and casting, use `bool`, `int` and `float`

```
<rule ref="Symfony3Custom.NamingConventions.ValidScalarTypeName" />
```

### Documentation

- Add PHPDoc blocks for all classes, methods, and functions

We added exceptions for functions `setUp`, `tearDown` and `tests` with no `@param` or `@return`
```
<rule ref="Symfony3Custom.Commenting.ClassComment" />
<rule ref="Symfony3Custom.Commenting.FunctionComment" />
```

We added exceptions for param comments
```
<rule ref="Symfony3Custom.Commenting.FunctionComment.MissingParamComment">
    <severity>0</severity>
</rule>
```

- Group annotations together so that annotations of the same type immediately follow each other,
 and annotations of a different type are separated by a single blank line

```
<rule ref="Symfony3Custom.Commenting.DocCommentGroupSameType" />
```

- Omit the `@return` tag if the method does not return anything

```
<rule ref="Symfony3Custom.Commenting.FunctionComment" />
```

- The `@package` and `@subpackage` annotations are not used

```
<rule ref="Symfony3Custom.Commenting.DocCommentForbiddenTags" />
```

## Others

- Add a single space after type casting

```
<rule ref="Generic.Formatting.SpaceAfterCast"/>
```

- Add a single space around assignement operator

```
<rule ref="Symfony3Custom.WhiteSpace.AssignementSpacing"/>
```

- Do not use spaces after `(` or `{` or before `)` or `}`

```
<rule ref="Symfony3Custom.WhiteSpace.CloseBracketSpacing"/>
<rule ref="Symfony3Custom.WhiteSpace.OpenBracketSpacing"/>
```

- Do not use multiple following blank lines

```
<rule ref="Symfony3Custom.WhiteSpace.EmptyLines"/>
```

- Do not call functions with variables passed by reference

```
<rule ref="Generic.Functions.CallTimePassByReference"/>
```

- Use lowercase for PHP functions

```
<rule ref="Squiz.PHP.LowercasePHPFunctions"/>
```

- Variable and methods have scope modifier

```
<rule ref="Squiz.Scope.MemberVarScope"/>
<rule ref="Symfony3Custom.Scope.MethodScope"/>
```

- No perl-style comments are used

```
<rule ref="PEAR.Commenting.InlineComment"/>
```
