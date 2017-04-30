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

- There MUST be one space after type hinting

```
<rule ref="Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterHint">
    <severity>5</severity>
</rule>
```

## From symfony

We mainly respect the [Symfony Standard](./symfony.md) but

- We do not respect these rules:

  - Add no space around the concatenation (`.`) operator
  - Declare all the arguments on the same line as the method/function name, no matter how many arguments there are

- We do not currently check these rules:

  - Always use identical comparison unless you need type juggling
  - Use Yoda conditions when checking a variable against an expression
  - Exception and error message strings must be concatenated using `sprintf`
  - Calls to `trigger_error` with type `E_USER_DEPRECATED` must be switched to opt-in via `@`operator
  - Do not use `else`, `elseif`, `break` after `if` and `case` conditions which return or throw something

## Others
### From Zend

- @TODO

```
<rule ref="Generic.Functions.OpeningFunctionBraceBsdAllman">
    <exclude name="Generic.Functions.OpeningFunctionBraceBsdAllman.BraceOnSameLine"/>
</rule>
```

- @TODO

```
<rule ref="Generic.PHP.DisallowShortOpenTag"/>
```

- @TODO

```
<rule ref="PEAR.Classes.ClassDeclaration"/>
```

- @TODO

```
<rule ref="Squiz.Functions.GlobalFunction"/>
```

- @TODO

```
<rule ref="Squiz.NamingConventions.ValidVariableName">
    <exclude name="Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore"/>
    <exclude name="Squiz.NamingConventions.ValidVariableName.ContainsNumbers"/>
</rule>
```

### Others

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
