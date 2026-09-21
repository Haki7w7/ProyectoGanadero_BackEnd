import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { AnimalesModule } from './animales/animales.module';

@Module({
  imports: [
    TypeOrmModule.forRoot({
      type: 'better-sqlite3',
      database: 'database.sqlite',
      autoLoadEntities: true,
      synchronize: true,  
    }),

    AnimalesModule,
    
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}