import { Link } from "@inertiajs/react";
import Dropdown from '@/Components/Dropdown';
import NavLink from "@/Components/NavLink";
import ApplicationLogo from "@/Components/ApplicationLogo";
import NavMenu from "@/Components/Menus/NavMenu";

const font="font-semibold text-gray-400 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-900 text-sm self-stretch"

const options = (auth, styles={
    link        : `${font} focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500`,
    templates   : {
        arrow       : {
            span        : `${font} font-medium rounded-r-md bg-white focus:outline-none transition ease-in-out duration-150 border rounded-r-md`,
            svg         : "h-4 w-4",
        },
        dropdown    : {
            label       : "flex w-full",
            trigger     : "flex",
        },
    },
    logo            : {
        label           : "block h-9 w-auto fill-current center text-gray-400",
    },
    products    : {
        link        : `flex self-stretch relative overscroll-auto overscroll-contain group/products text-sm`,
        wrapper         : {
            items           : "absolute hidden group-hover/products:block top-16 right-1 z-50 p-4 bg-white text-sm rounded-b-md shadow-md",
        },
    },
    auth        : {
        link        : "flex relative",
        wrapper         : {
            label           : `${font} block border px-2 w-full rounded-l-md`,
        }
    },
}) => {
    const templates = {
        decorative      : {
            arrow           : o => {
                const {styles, ...rest} = o;
                return (
                    <span className={styles?.templates?.arrow?.span}>
                        <svg className={styles?.templates?.arrow?.svg}
                             xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 10"
                             fill="currentColor">
                            <path fillRule="evenodd"
                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                  clipRule="evenodd" />
                        </svg>
                    </span>
                );
            },
        },
        recursive       : {
            Dropdown        : o => {
                const {key, type, label, items, wrapper, depth, templates, styles, ...rest} = o;
                return (
                    <Dropdown key={key} {...rest}>
                        <div className={styles?.templates?.dropdown?.label}>
                            {label}
                            <Dropdown.Trigger className={styles?.templates.dropdown?.trigger}>
                                {templates?.decorative?.arrow({styles:styles})}
                            </Dropdown.Trigger>
                        </div>
                        <Dropdown.Content>
                            <NavMenu contents={items}
                                     tag="Dropdown.Link"
                                     depth={depth+1}
                                     templates={templates}
                                     styles={styles} />
                        </Dropdown.Content>
                    </Dropdown>
                );
            },
        },
        plain           : {
            Link            : o => {
                const {key, type, url, label, active, ...rest} = o;
                return (
                    <Link   key={key}
                            href={route(url)}
                            {...rest}>
                        {label}
                    </Link>
                );
            },
            NavLink         : o => {
                const {key, type, url, label, active, ...rest} = o;
                return (
                    <NavLink key={key}
                             href={route(url)}
                             active={active && route().current(url)}
                             {...rest}>
                        {label}
                    </NavLink>
                );
            },
            "Dropdown.Link" : o => {
                const {key, type, url, label, active, ...rest} = o;
                return (
                    <Dropdown.Link key={key}
                                   href={route(url)}
                                   {...rest}>
                        {label}
                    </Dropdown.Link>
                );
            }
        },
    };

    const commonContents = {
        Logo                : {
            url                 : "home",
            label               : <ApplicationLogo className={styles?.logo?.label} />,
            className           : styles?.logo?.link ?? styles?.link,
        },
        Feeds               : {
            url                 : {
                url                 : "products",
                label               : "Feeds",
                type                : "NavLink",
                className           : styles?.link,
                inner               : auth.user ? "inner-user" : "inner-guest"
            },
            type                : "div",
            className           : styles?.products?.link,
            wrapper             : {
                items               : {
                    div                 : {
                        className           : styles?.products?.wrapper?.items,
                    }
                },
            },
            items               : {
                Pornstars           : {
                    url                 : "products.pornstars",
                    type                : "NavLink",
                    className           : styles?.pornstars?.link ?? styles?.link,
                },
            }
        },
    };

    const contents = auth.user
        ? {
            Logo                : commonContents.Logo,
            Dashboard           : {
                url                 : "dashboard",
                className           : styles?.dashboard?.link ?? styles?.link,
            },
            Products            : commonContents.Feeds,
            [auth.user?.name]   : {
                type                : "Dropdown",
                className           : styles?.auth?.link ?? styles?.link,
                wrapper             : {
                    label               : {span: {className: styles?.auth?.wrapper?.label}},
                },
                items               : {
                    Profile             : {
                        url                 : "profile.edit",
                        className           : styles?.profile?.link ?? styles?.link,
                    },
                    "Sign Out"          : {
                        url                 : "logout",
                        active              : false,
                        className           : styles?.signout?.link ?? styles?.link,
                        method              : "post",
                        as                  : "button"
                    }
                }
            },
        }
        : {
            ...commonContents,
            "Sign In"           : {
                url                 : "login",
                className           : styles?.signin?.link ?? styles?.link,
            },
            "Sign Up"           : {
                url                 : "register",
                className           : styles?.signup?.link ?? styles?.link,
            }
        };

    return {contents: contents, templates: templates, styles:styles};
};

export { options };