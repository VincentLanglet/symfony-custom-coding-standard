# PHP Coding Standard Rules
## From PSR2

We imported the [PSR2 Standard](./standards/psr2.md) with these overrides:

- There is not line length limit

```
<exclude ref="Generic.Files.LineLength">
```

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

- PhpDoc comments MUSE use an indent of 4 spaces, and MUST NOT use tabs for indenting.
```
<rule ref="Generic.WhiteSpace.ScopeIndent">
    <properties>
        <property name="ignoreIndentationTokens" type="array">
            <element value="T_COMMENT"/>
        </property>
    </properties>
</rule>
```

## From symfony

We mainly respect the [Symfony Standard](./standards/symfony.md) but
we do not respect this rule:

  - Declare all the arguments on the same line as the method/function name, no matter how many arguments there are

## Others
### Imported
*See `ruleset.xml` for up to date configuration.*

### Custom
- Some others checks are made about array (`=>` alignments and indentation)

```
<rule ref="SymfonyCustom.Array.ArrayDeclaration"/>
```

- Do not use blank lines after class openers `{`

```
<rule ref="SymfonyCustom.Classes.ClassDeclaration"/>
```

- Member var should have phpDoc with one blank line before

```
<rule ref="SymfonyCustom.Commenting.VariableComment"/>
```

- `use` keywords should be alphabetically sorted

```
<rule ref="SymfonyCustom.Namespaces.AlphabeticallySortedUse"/>
```

- Add a single space before `namespace` declaration

```
<rule ref="SymfonyCustom.Namespaces.NamespaceDeclaration"/>
```

- Unused `use` statement should be removed

```
<rule ref="SymfonyCustom.Namespaces.UnusedUse"/>
```

- Methods have scope modifier

```
<rule ref="SymfonyCustom.Scope.MethodScope"/>
```

- Do not use spaces after `(`, `{` or `[` and before `)`, `}` or `]`

```
<rule ref="SymfonyCustom.WhiteSpace.CloseBracketSpacing"/>
<rule ref="SymfonyCustom.WhiteSpace.OpenBracketSpacing"/>
```

- Add a single space around comment tag (`@var`, `@return`, `...`)

```
<rule ref="SymfonyCustom.WhiteSpace.DocCommentTagSpacing"/>
```

- Do not use multiple following blank lines

```
<rule ref="SymfonyCustom.WhiteSpace.EmptyLines"/>
```
