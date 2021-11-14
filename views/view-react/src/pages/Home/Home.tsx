import React, { useEffect, useState } from "react";
import { Item, ListGroup } from "../../components/ListGroup";
import { getUser } from "./services";

export const Home = () => {
    const [users, setUsers] = useState<Item[]>([]);

    useEffect(() => {
        getUser().then((users) => {
            setUsers(users)
        }).catch(() => {
            console.log('error');
        })
    }, []);

    return(
        <>
            <h1 className="display-1">Display 1</h1>
            <ListGroup items={users}/>
        </>
    )
}