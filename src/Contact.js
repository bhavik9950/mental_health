import React, { useState } from 'react';
import axios from 'axios';
import './health.css';
const Contact = () => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [query, setQuery] = useState('');
  const [responseMessage, setResponseMessage] = useState('');

  const handleSubmit = async (e) =>   {
    e.preventDefault();

    try {
      const formData = new URLSearchParams();
      formData.append('name', name);
      formData.append('email', email);
      formData.append('query', query);
     
      const response = await axios.post('http://localhost:8080/contact.php', formData, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
      },
      });
      setResponseMessage(response.data.message);
    } catch (error) {
      console.error('Error submitting form:', error);
      setResponseMessage('There was an error submitting your form.');
    }
  };

  return (
    <div className="contact-form">
      <h2 className='contact'>Contact Us</h2>
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <input
            type="text"
            className="form-control"
            placeholder="Name"
            value={name}
            name="name"
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>
        <div className="form-group">
          <input
            type="email"
            className="form-control"
            placeholder="Email"
            value={email}
            name="email"
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div className="form-group">
          <textarea
            className="form-control"
            rows="5"
            name="query"
            placeholder="Enter your query..."
            value={query}
            onChange={(e) => setQuery(e.target.value)}
          />
        </div>
        <button type="submit" className="btn btn-primary mt-2">
          Submit
        </button>
      </form>
      {responseMessage && <p>{responseMessage}</p>}
    </div>
  );
};

export default Contact;
