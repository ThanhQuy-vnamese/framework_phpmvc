import {ChangeEvent, FormEvent, useState} from "react";
import {UserService} from "../../services/UserService";

export const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const onChangeEmail = (ev: ChangeEvent<HTMLInputElement>) => {
        setEmail(ev.target.value);
    }

    const onChangePassword = (ev: ChangeEvent<HTMLInputElement>) =>{
        setPassword(ev.target.value);
    }

    const onSubmit = (ev: FormEvent) => {
        ev.preventDefault();
        const userService = new UserService();
        userService.login({email, password});
    }

    return (
        <form>
            <div className="form-group">
                <label htmlFor="exampleInputEmail1">Email address</label>
                <input type="email" className="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"
                       placeholder="Enter email" onChange={onChangeEmail} />
            </div>
            <div className="form-group">
                <label htmlFor="exampleInputPassword1">Password</label>
                <input type="password" className="form-control" id="exampleInputPassword1" placeholder="Password" onChange={onChangePassword}/>
            </div>
            <button type="submit" className="btn btn-primary" onClick={onSubmit}>Submit</button>
        </form>
    )
}
