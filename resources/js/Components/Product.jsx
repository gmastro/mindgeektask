import React from 'react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Link } from '@inertiajs/react';
import Details from './Details';

dayjs.extend(relativeTime);

const styles = {
    wrapper:"p-2 m-1 shadow-md rounded-lg bg-gray-200 hover:bg-orange-100",
    h3:     "text-lg text-orange-500 text-center",
    p:      "flex justify-between",
    noimg:  "shadow-md rounded-md mx-auto min-h-[344px] min-w-[234px]",
    img:    "shadow-md rounded-md mx-auto object-cover min-h-[344px]",
};

export default function Product({ auth, product }) {
    const {
        id,
        downloads,
        name,
        link,
        license,
        wlStatus,
        attributes,
        stats,
        aliases,
        created_at,
        updated_at,
        ...rest
    } = product;

    const cachedImageUrl = (hash) => ["/file/display", hash].join('/');

    const noImages = (dl) =>
        dl.length === 0
        &&  <figure className="text-center">
                <img alt="Apologies, no image"
                     className={styles.noimg}/>
                <figcaption>Image hotlink <span className="text-orange-500">none</span></figcaption>
            </figure>

    return (
        <div className={styles.wrapper}>
            <header>
                <h3 className={styles.h3}>{name}</h3>
            </header>
            {downloads.map((dl) => 
                <figure key={[rest.id, dl.url, 'thumbnail', 'cached'].join('-')}
                        className="text-center">
                    {/* or utilize picture tag/imgsrc attribute with dl.media, which, requires ',' split */}
                    <img src={cachedImageUrl(dl.url)}
                         className={styles.img}
                         alt="missing image" />
                    <figcaption>Image <Link href={dl.hotlink} className="text-orange-500">Hotlink</Link></figcaption>
                </figure>
            )}
            {noImages(downloads)}
            {auth.user
                && <>
                    <p className={[styles.p, "mt-2"].join(" ")}>
                        <b>Link</b>
                        <Link href={link} className="text-orange-500">Link</Link>
                    </p>
                    <p className={styles.p}>
                        <b>License</b>
                        <span>{license}</span>
                    </p>
                    <p className={styles.p}>
                        <b>Status</b>
                        <span>{wlStatus}</span>
                    </p>
                    <Details summary="Attributes" details={attributes} groupId={id} isOpen={true} />
                    <Details summary="Stats" details={stats} groupId={id} />
                    <Details summary="Aliases" details={aliases} groupId={id} />
                </>
            }
            <p className={styles.p}>
                <b>Created</b>
                <span>{dayjs(created_at).fromNow()}</span>
            </p>
            <p className={styles.p}>
                <b>Updated</b>
                <span>{updated_at != null ? dayjs(updated_at).fromNow() : "-"}</span>
            </p>
        </div>
    );
}