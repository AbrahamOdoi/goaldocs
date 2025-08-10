# GoalDocs Mobile API Testing Guide

## 🚀 Quick Start

The GoalDocs Mobile API is now running at: `http://localhost:8000/api`

## 📱 API Endpoints Overview

### Authentication Endpoints
- `POST /api/mobile/login` - Mobile user login
- `POST /api/mobile/logout` - Mobile user logout
- `POST /api/mobile/refresh-session` - Refresh session

### User Management
- `GET /api/mobile/profile` - Get user profile
- `GET /api/mobile/stats` - Get user statistics

### File Management
- `GET /api/mobile/files` - Get user files
- `GET /api/mobile/folders` - Get user folders
- `GET /api/mobile/files/{id}` - Get file details
- `GET /api/mobile/files/{id}/download` - Download file
- `GET /api/mobile/files/{id}/preview` - Preview file
- `POST /api/mobile/upload` - Upload file
- `POST /api/mobile/folders` - Create folder

### Search & Analytics
- `GET /api/mobile/search` - Search files and folders
- `GET /api/docs` - API documentation

## 🧪 Testing with Postman

### 1. Setup Postman Collection

Create a new collection called "GoalDocs Mobile API" and add these requests:

#### **Login Request**
```
POST http://localhost:8000/api/mobile/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password",
    "device_id": "test-device-001",
    "device_name": "Test iPhone",
    "device_type": "ios"
}
```

#### **Get Profile**
```
GET http://localhost:8000/api/mobile/profile
Authorization: Bearer {token_from_login}
X-Device-ID: test-device-001
```

#### **Get Files**
```
GET http://localhost:8000/api/mobile/files?per_page=10
Authorization: Bearer {token_from_login}
X-Device-ID: test-device-001
```

#### **Upload File**
```
POST http://localhost:8000/api/mobile/upload
Authorization: Bearer {token_from_login}
X-Device-ID: test-device-001
Content-Type: multipart/form-data

file: [select file]
folder_id: [optional]
name: [optional]
```

#### **Search Files**
```
GET http://localhost:8000/api/mobile/search?query=document&type=files
Authorization: Bearer {token_from_login}
X-Device-ID: test-device-001
```

## 🧪 Testing with cURL

### 1. Login and Get Token
```bash
curl -X POST http://localhost:8000/api/mobile/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password",
    "device_id": "test-device-001",
    "device_name": "Test iPhone",
    "device_type": "ios"
  }'
```

### 2. Get User Profile
```bash
curl -X GET http://localhost:8000/api/mobile/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "X-Device-ID: test-device-001"
```

### 3. Get Files
```bash
curl -X GET "http://localhost:8000/api/mobile/files?per_page=5" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "X-Device-ID: test-device-001"
```

### 4. Upload File
```bash
curl -X POST http://localhost:8000/api/mobile/upload \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "X-Device-ID: test-device-001" \
  -F "file=@/path/to/your/file.pdf"
```

### 5. Search Files
```bash
curl -X GET "http://localhost:8000/api/mobile/search?query=test&type=all" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "X-Device-ID: test-device-001"
```

## 📱 Testing with Mobile App Simulators

### iOS Simulator Testing
1. **Install Xcode** (if on macOS)
2. **Create a simple iOS app** using SwiftUI or React Native
3. **Use the API endpoints** in your mobile app code

### Android Emulator Testing
1. **Install Android Studio**
2. **Create a simple Android app** using Kotlin/Java or React Native
3. **Use the API endpoints** in your mobile app code

## 🧪 Testing with React Native (Quick Setup)

### 1. Create React Native App
```bash
npx react-native init GoalDocsMobile
cd GoalDocsMobile
```

### 2. Install Dependencies
```bash
npm install axios @react-native-async-storage/async-storage
```

### 3. Create API Service
```javascript
// src/services/api.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://localhost:8000/api/mobile';

class ApiService {
  constructor() {
    this.api = axios.create({
      baseURL: API_BASE_URL,
      timeout: 10000,
    });

    // Add request interceptor for auth token
    this.api.interceptors.request.use(async (config) => {
      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      config.headers['X-Device-ID'] = 'react-native-device';
      return config;
    });
  }

  async login(email, password) {
    try {
      const response = await this.api.post('/login', {
        email,
        password,
        device_id: 'react-native-device',
        device_name: 'React Native App',
        device_type: 'ios', // or 'android'
      });

      if (response.data.success) {
        await AsyncStorage.setItem('auth_token', response.data.data.token);
        await AsyncStorage.setItem('user_data', JSON.stringify(response.data.data.user));
      }

      return response.data;
    } catch (error) {
      throw error.response?.data || error.message;
    }
  }

  async getProfile() {
    try {
      const response = await this.api.get('/profile');
      return response.data;
    } catch (error) {
      throw error.response?.data || error.message;
    }
  }

  async getFiles(params = {}) {
    try {
      const response = await this.api.get('/files', { params });
      return response.data;
    } catch (error) {
      throw error.response?.data || error.message;
    }
  }

  async uploadFile(file, folderId = null, name = null) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      if (folderId) formData.append('folder_id', folderId);
      if (name) formData.append('name', name);

      const response = await this.api.post('/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error.message;
    }
  }

  async search(query, type = 'all') {
    try {
      const response = await this.api.get('/search', {
        params: { query, type },
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error.message;
    }
  }

  async logout() {
    try {
      await this.api.post('/logout');
      await AsyncStorage.removeItem('auth_token');
      await AsyncStorage.removeItem('user_data');
    } catch (error) {
      console.error('Logout error:', error);
    }
  }
}

export default new ApiService();
```

### 4. Create Login Screen
```javascript
// src/screens/LoginScreen.js
import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
} from 'react-native';
import ApiService from '../services/api';

const LoginScreen = ({ onLogin }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    setLoading(true);
    try {
      const result = await ApiService.login(email, password);
      if (result.success) {
        onLogin(result.data.user);
      } else {
        Alert.alert('Error', result.message);
      }
    } catch (error) {
      Alert.alert('Error', error.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>GoalDocs Mobile</Text>
      <TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        autoCapitalize="none"
      />
      <TextInput
        style={styles.input}
        placeholder="Password"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />
      <TouchableOpacity
        style={styles.button}
        onPress={handleLogin}
        disabled={loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Logging in...' : 'Login'}
        </Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
    backgroundColor: '#f5f5f5',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 30,
  },
  input: {
    backgroundColor: 'white',
    padding: 15,
    borderRadius: 8,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default LoginScreen;
```

## 🧪 Testing with Flutter (Alternative)

### 1. Create Flutter App
```bash
flutter create goaldocs_mobile
cd goaldocs_mobile
```

### 2. Add Dependencies
```yaml
# pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^0.13.5
  shared_preferences: ^2.0.15
```

### 3. Create API Service
```dart
// lib/services/api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'http://localhost:8000/api/mobile';
  
  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    
    return {
      'Content-Type': 'application/json',
      'X-Device-ID': 'flutter-device',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<Map<String, dynamic>> login(
    String email, 
    String password,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await _getHeaders(),
      body: json.encode({
        'email': email,
        'password': password,
        'device_id': 'flutter-device',
        'device_name': 'Flutter App',
        'device_type': 'android',
      }),
    );

    final data = json.decode(response.body);
    
    if (data['success']) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', data['data']['token']);
      await prefs.setString('user_data', json.encode(data['data']['user']));
    }
    
    return data;
  }

  static Future<Map<String, dynamic>> getProfile() async {
    final response = await http.get(
      Uri.parse('$baseUrl/profile'),
      headers: await _getHeaders(),
    );
    
    return json.decode(response.body);
  }

  static Future<Map<String, dynamic>> getFiles({
    int perPage = 20,
    String? search,
    int? folderId,
    String? type,
  }) async {
    final queryParams = <String, String>{
      'per_page': perPage.toString(),
    };
    
    if (search != null) queryParams['search'] = search;
    if (folderId != null) queryParams['folder_id'] = folderId.toString();
    if (type != null) queryParams['type'] = type;

    final response = await http.get(
      Uri.parse('$baseUrl/files').replace(queryParameters: queryParams),
      headers: await _getHeaders(),
    );
    
    return json.decode(response.body);
  }
}
```

## 🧪 Testing Checklist

### Authentication Testing
- [ ] Login with valid credentials
- [ ] Login with invalid credentials
- [ ] Logout functionality
- [ ] Token expiration handling
- [ ] Session refresh

### File Management Testing
- [ ] Get files list
- [ ] Upload files
- [ ] Download files
- [ ] Preview files
- [ ] Create folders
- [ ] Search files and folders

### Error Handling Testing
- [ ] Network errors
- [ ] Invalid file uploads
- [ ] Unauthorized access
- [ ] Server errors

### Performance Testing
- [ ] Large file uploads
- [ ] Multiple concurrent requests
- [ ] Offline handling
- [ ] Memory usage

## 🔧 Troubleshooting

### Common Issues

1. **CORS Errors**: Make sure your Laravel app allows mobile app requests
2. **Network Issues**: Ensure the API server is running and accessible
3. **Authentication Errors**: Check token format and expiration
4. **File Upload Issues**: Verify file size limits and permissions

### Debug Commands

```bash
# Check API server status
curl http://localhost:8000/api/docs

# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear cache if needed
php artisan cache:clear
php artisan config:clear
```

## 📱 Next Steps

1. **Test all API endpoints** using the methods above
2. **Create a simple mobile app** using React Native or Flutter
3. **Implement push notifications** testing
4. **Add offline capabilities** testing
5. **Performance testing** with real devices

The Mobile API is now fully functional and ready for mobile app development!
