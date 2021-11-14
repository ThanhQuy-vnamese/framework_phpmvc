import React, {VFC} from "react";
import clsx from "clsx";

export interface Item {
    name: string
    isActive: boolean;
}

export interface ListGroupProps {
    items: Item[];
}

export const ListGroup: VFC<ListGroupProps> = ({items}) => {
    return (
        <ul className="list-group">
            {items.map((item, index) => {
                const itemCss = clsx('list-group-item', {
                    active: item.isActive,
                })

                return (
                    <li key={index} className={itemCss}>Cras justo odio</li>
                );
            })}
        </ul>
    );
}
