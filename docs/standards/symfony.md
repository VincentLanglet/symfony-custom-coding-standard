# Symfony standard
From [symfony standard](http://symfony.com/doc/current/contributing/code/standards.html)

## Structure
- Add a single space after each comma delimiter

```
<rule ref="SymfonyCustom.Array.ArrayDeclaration"/>
```

- Add a single space around binary operators (`==`, `&&`, `...`)
 with the exception of the concatenation (`.`) operator

```
<rule ref="Squiz.WhiteSpace.LogicalOperatorSpacing"/>
<rule ref="Squiz.WhiteSpace.OperatorSpacing">
    <properties>
        <property name="ignoreNewlines" value="true"/>
    </properties>
</rule>
<rule ref="Squiz.Strings.ConcatenationSpacing">
    <properties>
        <property name="ignoreNewlines" value="true"/>
    </properties>
</rule>
```

- Place unary operators (`!`, `--`, `...`) adjacent to the affected variable

```
<rule ref="SymfonyCustom.WhiteSpace.SpaceUnaryOperatorSpacing"/>
```

- Always use identical comparison unless you need type juggling

```
<rule ref="SymfonyCustom.Formatting.StrictComparison"/>
```

- Use Yoda conditions when checking a variable against an expression

```
<rule ref="SymfonyCustom.Formatting.YodaCondition"/>
```

- Add a comma after each array item in a multi-line array, even after the last one

```
<rule ref="SymfonyCustom.Array.ArrayDeclaration"/>
```

- Add a blank line before return statements,
 unless the return is alone inside a statement-group (like an `if` statement)

```
<rule ref="SymfonyCustom.Formatting.BlankLineBeforeReturn"/>
```

- Use `return null` when a function explicitly returns null values
 and use `return` when the function returns void values

```
<rule ref="SymfonyCustom.Commenting.FunctionComment"/>
```

- Use braces to indicate control structure body regardless of the number of statements it contains

Covered by `PSR2`

- Define one class per file

Covered by `PSR2`

- Declare the class inheritance and all the implemented interfaces on the same line as the class name

Covered by `PSR2`

- Declare class properties before methods

```
<rule ref="SymfonyCustom.Classes.PropertyDeclaration"/>
```

- Declare public methods first, then protected ones and finally private ones.
 The exceptions to this rule are the class constructor and the `setUp()` and `tearDown()` methods of PHPUnit tests,
  which must always be the first methods to increase readability

```
<rule ref="SymfonyCustom.Functions.ScopeOrder"/>
```

- Declare all the arguments on the same line as the method/function name, no matter how many arguments there are

Not checked because of the limit of 120 characters per line

- Use parentheses when instantiating classes regardless of the number of arguments the constructor has

```
<rule ref="SymfonyCustom.Objects.ObjectInstantiation"/>
```

- Exception and error message strings must be concatenated using `sprintf`

```
<rule ref="SymfonyCustom.Errors.ExceptionMessage"/>
```

- Calls to `trigger_error` with type `E_USER_DEPRECATED` must be switched to opt-in via `@`operator

```
<rule ref="SymfonyCustom.Errors.UserDeprecated"/>
```

- Do not use `else`, `elseif`, `break` after `if` and `case` conditions which return or throw something

```
<rule ref="SymfonyCustom.Formatting.ConditionalReturnOrThrowSniff"/>
```

- Do not use spaces around `[` offset accessor and before `]` offset accessor

```
<rule ref="Squiz.Arrays.ArrayBracketSpacing"/>
```

### Naming Conventions

- Use camelCase, not underscores, for variable, function and method names, arguments

Covered by `PSR2` completed by
```
<rule ref="Squiz.NamingConventions.ValidVariableName">
    <exclude name="Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore"/>
    <exclude name="Squiz.NamingConventions.ValidVariableName.ContainsNumbers"/>
</rule>
```

- Use namespaces for all classes

Covered by `PSR1` completed by
```
<rule ref="Squiz.Classes.ValidClassName"/>
```

- Prefix abstract classes with `Abstract`

```
<rule ref="SymfonyCustom.NamingConventions.ValidClassName"/>
```

- Suffix interfaces with `Interface`

```
<rule ref="SymfonyCustom.NamingConventions.ValidClassName"/>
```

- Suffix traits with `Trait`

```
<rule ref="SymfonyCustom.NamingConventions.ValidClassName"/>
```

- Suffix exceptions with `Exception`

```
<rule ref="SymfonyCustom.NamingConventions.ValidClassName"/>
```

- Use alphanumeric characters and underscores for file names

```
<rule ref="SymfonyCustom.NamingConventions.ValidFileName"/>
```

- For type-hinting in PHPDocs and casting, use `bool`, `int` and `float`

```
<rule ref="SymfonyCustom.NamingConventions.ValidScalarTypeName"/>
```

### Documentation

- Add PHPDoc blocks for all classes, methods, and functions

We added exceptions for functions with no `@param` or `@return`
```
<rule ref="SymfonyCustom.Commenting.ClassComment"/>
<rule ref="SymfonyCustom.Commenting.FunctionComment"/>
```

We added exceptions for param comments
```
<rule ref="SymfonyCustom.Commenting.FunctionComment.MissingParamComment">
    <severity>0</severity>
</rule>
```

- Group annotations together so that annotations of the same type immediately follow each other,
 and annotations of a different type are separated by a single blank line

```
<rule ref="SymfonyCustom.Commenting.DocCommentGroupSameType"/>
```

- Omit the `@return` tag if the method does not return anything

```
<rule ref="SymfonyCustom.Commenting.FunctionComment"/>
```

- The `@package` and `@subpackage` annotations are not used

```
<rule ref="SymfonyCustom.Commenting.DocCommentForbiddenTags"/>
```
