import { ListGroup } from '../ListGroup';

export default {
    component: ListGroup,
    title: 'Components/ListGroup',
}

export const Default = () => {
    const items = [
        {
            name: 'Cras justo odio',
            isActive: true,
        },
        {
            name: 'Dapibus ac facilisis in',
            isActive: false,
        },
        {
            name: 'Morbi leo risus',
            isActive: false,
        },
        {
            name: 'Porta ac consectetur ac',
            isActive: false,
        },
    ];
    return <ListGroup items={items}/>;
}