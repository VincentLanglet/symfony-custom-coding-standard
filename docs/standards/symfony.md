# Symfony standard
From [symfony standard](http://symfony.com/doc/current/contributing/code/standards.html)

## Structure
- Add a single space after each comma delimiter

```
<rule ref="Symfony3Custom.Array.ArrayDeclaration" />
```

- Add a single space around binary operators (`==`, `&&`, `...`)

```
<rule ref="Squiz.WhiteSpace.OperatorSpacing" />
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

- Always use identical comparison unless you need type juggling

Not covered

- Use Yoda conditions when checking a variable against an expression

Not covered

- Add a comma after each array item in a multi-line array, even after the last one

```
<rule ref="Symfony3Custom.Array.ArrayDeclaration" />
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

- Declare all the arguments on the same line as the method/function name, no matter how many arguments there are

Not checked because of the limit of 120 characters per line

- Use parentheses when instantiating classes regardless of the number of arguments the constructor has

```
<rule ref="Symfony3Custom.Objects.ObjectInstantiation" />
```

- Exception and error message strings must be concatenated using `sprintf`

Not covered

- Calls to `trigger_error` with type `E_USER_DEPRECATED` must be switched to opt-in via `@`operator

Not covered

- Do not use `else`, `elseif`, `break` after `if` and `case` conditions which return or throw something

Not covered

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
