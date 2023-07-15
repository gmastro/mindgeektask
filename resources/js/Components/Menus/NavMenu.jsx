import React, { createElement } from "react";
import md5 from 'md5';

export default function NavMenu({
    contents,
    tag="NavLink",
    depth = 0,
    inner = '',
    forcedWrapper = null,
    templates = {},
    styles = {}
}) {
    /**
     * Null data type rename
     *
     * Using instead of object some foo name for null
     *
     * @var     {String} rejectedDataTypeName
     */
    const rejectedDataTypeName = "svounia";

    /**
     * NavMenu Default Templates
     *
     * Holds all those tem
     *
     * @var     {Object} supported
     */
    const supported = {
        recursive: {
            datalist: "option",
            div: true,
            dt: ["dl", "dd"],
            menu: "li",
            ul: "li",
        },
        plain: {
            a: o => {
                const {key, url, label, active, ...rest} = o;
                return (<a key={key} href={url} {...rest}>{label}</a>);
            },
        }
    };

    /**
     * All templates
     *
     * Merges all plain and recursive template nodes for display
     *
     * @var     {Object} all
     */
    const all = {
        recursive: {
            ...(templates?.recursive ?? {}),
            ...(supported.recursive),
        },
        plain: {
            ...(templates?.plain ?? {}),
            ...(supported.plain),
        }
    };

    /**
     * Key Generator
     *
     * Adds a unique identifier for all those added menus
     *
     * @param   {String} label Node label
     * @param   {Number} index Index from the itterator
     * @returns {String}
     */
    const keyGenerator = (label, index) => `links-${md5(label)}-${depth}-${inner}-${index}`;

    /**
     * Type of Element
     *
     * Replace type of for null, and get all other data types
     *
     * @param   {*} n 
     * @returns {String}
     */
    const isOfType = (n = null) => n == null ? rejectedDataTypeName : typeof n;

    /**
     * Wrapper Elemenent
     *
     * Sets a wrapper element before rendered type
     *
     * @param   {Object|String} o Wrapping for the given node, if defined
     * @param   {*} children Any type of node within the wrapper
     * @returns 
     */
    const wrapping = (o, ...children) => {
        const isType = isOfType(o);

        try {
            const wrapTag = {
                object: Object.keys(o)[0],
                string: o,
            }[isType] ?? null;

            return {
                object: createElement(wrapTag, o[wrapTag], ...children),
                string: createElement(wrapTag, null, ...children)
            }[isType];
        } catch (ex) {
            return children;
        }
    };

    /**
     * After Template
     *
     * Places a label before wrapper element. The latter contains the children
     * 
     * @param   {Object} o Template data
     * @returns {Element}
     */
    const beforeTemplate = (o) => {
        const {label, type, items, force = forcedWrapper, wrapper, ...rest} = o;
        return (
            <>
                {label}
                {createElement(
                    type,
                    rest,
                    wrapping(
                        wrapper,
                        <NavMenu contents={items} depth={depth+1} forcedWrapper={force} templates={templates} styles={styles} />
                    )
                )}
            </>
        );
    };

    /**
     * Wrapper Template
     *
     * Places the label and the children inside wrapper element
     * 
     * @param   {Object} o Template data
     * @returns {Element}
     */
    const wrapperTemplate = (o) => {
        const {label, type, items, force = forcedWrapper, wrapper, ...rest} = o;
        return createElement(
            type,
            rest,
            label,
            wrapping(
                wrapper,
                <NavMenu contents={items} depth={depth+1} forcedWrapper={force} templates={templates} styles={styles} />
            )
        );
    };

    /**
     * After Template
     *
     * Places a label after wrapper element. The latter contains the children
     * 
     * @param   {Object} o Template data
     * @returns {Element}
     */
    const afterTemplate = (o) => {
        const {label, type, items, force = forcedWrapper, wrapper, ...rest} = o;
        return (
            <>
                {createElement(
                    type,
                    rest,
                    wrapping(
                        wrapper,
                        <NavMenu contents={items} depth={depth+1} forcedWrapper={force} templates={templates} styles={styles} />
                    )
                )}
                {label}
            </>
        );
    };

    /**
     * Parent Label As Link
     *
     * Checks if the parent label has a defined url property, thus wrap it within the link.
     *
     * @param   {Object} props 
     * @returns {Element}
     */
    const labelAsMenuLeaf = (props) => {
        const {url, label, wrapper} = props,
              urlType = isOfType(url),
              items = {
                  string: {[label]: {label: label, url: url}},
                  object: {[label]: {label: label, ...url}},
              }[urlType] ?? null;

        if(items == null) {
            return wrapping(wrapper, label);
        }

        return wrapping(
            wrapper,
            <NavMenu contents={items} depth={depth} inner="inner" templates={templates} styles={styles} />
        );
    };

    /**
     * Recurive structure
     *
     * Renders both parent and children. The children are rendered via recursion
     *
     * @param   {Object} props Parent properties
     * @returns {Element}
     */
    const menuParent = (props) => {
        const {
                url,
                key,
                label,
                type,
                position = "wrapped",
                items,
                wrapper:{label:labelWrapper = null, items:itemsWrapper = null} = {},
                ...rest
            } = props,
            urlLabel = labelAsMenuLeaf({url:url, label: label, wrapper:labelWrapper}),
            isSupported = isOfType(supported.recursive?.[type]) != rejectedDataTypeName;
        
        try {
            if (!isSupported) {
                return all.recursive[type]({...props, label:urlLabel, wrapper:itemsWrapper, depth:depth, templates:templates, styles:styles});
            }

            const data = {...props, label:urlLabel, wrapper:itemsWrapper, force: {
                string  : all.recursive[type],
                array   : all.recursive[type][0],
                boolean : null,
            }[isOfType(all.recursive[type])]};

            return {
                before  : beforeTemplate(data),
                wrapper : wrapperTemplate(data),
                after   : afterTemplate(data),
            }[position] ?? wrapperTemplate(data);
        } catch (error) {
            console.error(`Missing type/tag: "${type}". You should add the component as property into "templates.recursive.${type}"`)
            return (<></>);
        }
    };

    /**
     * Link Structure
     *
     * Renders a link, no recursion and no children
     *
     * @param   {Object} props Link properties
     * @returns {Element}
     */
    const menuLeaf = (props) => {
        const {
            type,
            active = true,
            wrapper = forcedWrapper,
        } = props;

        try {
            return wrapping(wrapper, all.plain[type]({...props, active:active, wrapper:wrapper}));
        } catch (ex) {
            console.error(`Missing type/tag: "${type}". You should add the component as property into "templates.plain.${type}"`)
            return (<></>);
        }
    };

    return (
        <>
            {Object.keys(contents).map( (label, index) => {
                const params = contents[label],
                    key = keyGenerator(label, index),
                    hasChildren = params.items != undefined,
                    {type = (hasChildren ? "div" : tag), ...rest} = params,
                    data = {type:type, key:key, label:label, ...rest};

                return hasChildren
                    ? menuParent(data)
                    : menuLeaf(data);
            })}
        </>
    );
}

