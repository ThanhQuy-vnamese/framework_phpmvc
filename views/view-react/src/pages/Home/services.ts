import axios from "axios";

export const getUser = async () => {
    const url = `${process.env.REACT_APP_PREFIX_API}api/users`;

    const response = await axios.get(url, {
        responseType: 'json'
    });
    return response.data;
}
