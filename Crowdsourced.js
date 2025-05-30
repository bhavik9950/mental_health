import React, { useState, useEffect } from 'react';
import './health.css';
import PostForm from './post-form'; 
import Feed from './Feed'; 
import Contact from './Contact'; 
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom';

// Simple Home Page component
const HomePage = () => {
  const txt = 'Welcome to Mental Health Support😊';
  const [visibleText, setText] = useState('');

  useEffect(() => {
    let char = 1; 
    let timer; 

    const animate = () => {
      timer = setInterval(() => { 
        setText(txt.slice(0, char));
        char += 1;
        if (char > txt.length) {
          clearInterval(timer);
        }
      }, 100);
    };

    animate(); 

    return () => clearInterval(timer); // Cleanup function to clear the interval
  }, []); // Ensure it only runs on mount

  const healthQuotes = [
    "It's okay to not be okay. Your mental health matters, and reaching out for help is a sign of strength, not weakness.",
    "Healing is not linear. Some days will be better than others, and that's part of the process. Be patient with yourself.",
    "You are not your thoughts. You are the observer of your thoughts, and you have the power to change them.",
    "Just because no one else can heal or do your inner work for you, doesn't mean you can, should, or need to do it alone. — Lisa Olivera",
    "Mental health is just as important as physical health. Take time to nurture your mind, because it deserves care too."
  ];
  const [visibleQuote,setQuote]=useState('');
  useEffect(() => {
    let currentQuote = 0;
    const timer = setInterval(() => {
      setQuote(healthQuotes[currentQuote]);
      currentQuote += 1;
      if (currentQuote >= healthQuotes.length) {
        currentQuote = 0;
      }
    }, 8000); 
  
    return () => clearInterval(timer); 
  }, []);
  
  return (
    <div className="home-page">
     <h1 id="heading" className="welcome-message">{visibleText}</h1>
      <p className="platform-description">This platform allows users to share and seek advice on mental health issues anonymously.</p>
       <strong className='quote'>
        {visibleQuote}
       </strong>
    </div>
  );
};
const Navbar = () => {
  const [isOpen, setIsOpen] = useState(false);

  const toggleNavbar = () => {
    setIsOpen(!isOpen);
  };

  const closeNavbar = () => {
    setIsOpen(false);
  };

  return (
    <div className="container-fluid">
    <nav className="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div className="container-fluid">
        <Link className="navbar-brand" to="/">Mental Health Support</Link>
        <button
          className="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span className="navbar-toggler-icon"></span>
        </button>

        <div className="collapse navbar-collapse" id="navbarNav">
          <ul className="navbar-nav ms-auto">
            <li className="nav-item">
              <Link className="nav-link" to="/">Home</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/post-form">Post Form</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/feed">Feed</Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/contact">Contact</Link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
  );
};


// Main Application Component with Routing
const Help = () => {
  return (
    <Router>
      <Navbar />
      <div className="container" id='container'>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/post-form" element={<PostForm />} />
          <Route path="/feed" element={<Feed />} />
          <Route path="/contact" element={<Contact />} />
        </Routes>
        <footer id='copyright'>
  <p>&copy; 2024 Bhavishya's Mental Health Support Platform. All Rights Reserved.</p>
</footer>
      </div>

    </Router>
    
  );
};

export default Help;
