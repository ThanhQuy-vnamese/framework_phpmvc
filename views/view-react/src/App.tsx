import React from 'react';
import './App.css';
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
} from "react-router-dom";
import {Home} from "./pages/Home";
import {Login} from "./pages/Login/Login";

function App() {
    return (
        <Router>
            <div>
                <ul>
                    <Link to="/">
                        <li>
                            Home
                        </li>
                    </Link>
                    <Link to="/login">
                        <li>
                            Login
                        </li>
                    </Link>
                </ul>
                <Switch>
                    <Route path="/" exact component={Home}/>
                    <Route path="/login" component={Login}/>
                </Switch>
            </div>
        </Router>
    );
}

export default App;
