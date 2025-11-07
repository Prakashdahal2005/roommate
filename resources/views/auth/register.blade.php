<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="{{ route('register.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="password_confirmation">Confirm Password:</label><br>
        <input type="password" id="password_confirmation" name="password_confirmation" required><br><br>

        <strong>Profile Information</strong><br>
        <input type="text" name="display_name" placeholder="Display Name" value="{{ old('display_name') }}" required>
    Upload your photo: <input type="file" name="profile_picture" accept="image/*">
    <textarea name="bio" placeholder="Bio">{{ old('bio') }}</textarea>
    <input type="number" name="age" placeholder="Age" value="{{ old('age') }}" required>

    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="male" {{ old('gender')=='male' ? 'selected':'' }}>Male</option>
        <option value="female" {{ old('gender')=='female' ? 'selected':'' }}>Female</option>
        <option value="other" {{ old('gender')=='other' ? 'selected':'' }}>Other</option>
    </select>

    <input type="number" name="budget_min" placeholder="Min Budget" value="{{ old('budget_min') }}" required>
    <input type="number" name="budget_max" placeholder="Max Budget" value="{{ old('budget_max') }}" required>
    <input type="date" name="move_in_date" value="{{ old('move_in_date') }}">

    <select name="cleanliness" required>
        <option value="">Cleanliness</option>
        <option value="very_clean" {{ old('cleanliness')=='very_clean'?'selected':'' }}>Very Clean</option>
        <option value="clean" {{ old('cleanliness')=='clean'?'selected':'' }}>Clean</option>
        <option value="average" {{ old('cleanliness')=='average'?'selected':'' }}>Average</option>
        <option value="messy" {{ old('cleanliness')=='messy'?'selected':'' }}>Messy</option>
    </select>

    <select name="schedule" required>
        <option value="">Schedule</option>
        <option value="morning_person" {{ old('schedule')=='morning_person'?'selected':'' }}>Morning Person</option>
        <option value="night_owl" {{ old('schedule')=='night_owl'?'selected':'' }}>Night Owl</option>
        <option value="flexible" {{ old('schedule')=='flexible'?'selected':'' }}>Flexible</option>
    </select>

    <label><input type="checkbox" name="smokes" value="1" {{ old('smokes')?'checked':'' }}> Smokes</label>
    <label><input type="checkbox" name="pets_ok" value="1" {{ old('pets_ok')?'checked':'' }}> Pets OK</label>
    <label><input type="checkbox" name="is_active" value="1" {{ old('is_active')?'checked':'' }}> Active</label>

<<<<<<< Updated upstream
        <button type="submit">Register</button>
    </form>
    Already have an account? <a href="{{ route('login') }}">Login here</a>
=======
                    <div class="form-actions" style="align-items:center;">
                        <div style="font-size:.90rem; color:#6B7280;">
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="checkbox" name="terms" style="width:16px;height:16px;" {{ old('terms') ? 'checked' : '' }} />
                                <span>I agree to the <a href="#" class="helper-link">terms</a></span>
                            </label>
                        </div>

                        <div>
                            @if (Route::has('login'))
                                <a class="helper-link" href="{{ route('login') }}">Sign in</a>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top:1rem; display:flex; gap:8px; justify-content:space-between; align-items:center;">
                        <button type="submit" class="btn btn-primary" style="min-width:140px;">Create account</button>
                        <a href="{{ url('/') }}" class="btn btn-outline" style="padding:10px 12px;">Back to home</a>
                    </div>

                    <!-- social sign-up removed -->
                </form>
            </div>
        </div>
    </div>
>>>>>>> Stashed changes
</body>
</html>


